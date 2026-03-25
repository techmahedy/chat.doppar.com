<?php

namespace App\Http\Controllers;

use Phaseolies\Utilities\Attributes\Route;
use Phaseolies\Utilities\Attributes\Mapper;
use Phaseolies\Http\Request;
use Doppar\AI\Agent;
use Doppar\AI\Vector\Vector;
use Doppar\AI\Store\CacheStore;
use Doppar\AI\AgentFactory\Agent\OpenAI;
use App\Http\Controllers\Controller;

#[Mapper(prefix: 'ai', middleware: ['throttle:60,1'])]
class WelcomeController extends Controller
{
    private string $vectorFile;
    private string $storePath;

    public function __construct()
    {
        $this->vectorFile = public_path('vector_docs_cache.json');
        $this->storePath  = storage_path('doppar_ai_conversations');
    }

    private function conversationKey(Request $request): string
    {
        $chatId = trim((string) ($request->get('chat_id', '')));
        if ($chatId !== '') {
            return 'doppar-ai-' . md5($chatId);
        }
        $session = $request->session()->getId() ?? session_id() ?? 'guest';
        return 'doppar-ai-' . md5($session);
    }

    private function loadDocs(): array
    {
        static $docs = null;
        if ($docs !== null) return $docs;
        if (!file_exists($this->vectorFile)) {
            $docs = [];
            return $docs;
        }
        $decoded = json_decode(file_get_contents($this->vectorFile), true);
        $docs = is_array($decoded) ? $decoded : [];
        return $docs;
    }

    private const EXCLUDED_PATHS = [
        'README.md',
        'readme.md',
        'CONTRIBUTING.md',
        'contributing.md',
        'CHANGELOG.md',
        'changelog.md',
        'LICENSE',
        'license',
        'CODE_OF_CONDUCT.md',
    ];

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = $na = $nb = 0.0;
        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $na  += $a[$i] * $a[$i];
            $nb  += $b[$i] * $b[$i];
        }
        if ($na === 0.0 || $nb === 0.0) return 0.0;
        return $dot / (sqrt($na) * sqrt($nb));
    }

    private function generateContext(string $question): array
    {
        $docs = $this->loadDocs();
        if (empty($docs)) {
            return ['context' => '', 'error' => 'Vector docs not found at: ' . $this->vectorFile];
        }

        $first = $docs[0] ?? [];
        if (!isset($first['vector'], $first['content'])) {
            return ['context' => '', 'error' => 'Chunk keys invalid. Found: ' . implode(',', array_keys($first))];
        }

        try {
            $embedAgent = Agent::using(OpenAI::class)->withKey(env('OPENAI_API_KEY'));

            $vector = Vector::embedding(
                $embedAgent,
                env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
                $question
            );

            if (empty($vector)) {
                return ['context' => '', 'error' => 'Embedding returned empty vector'];
            }

            $threshold = (float) env('RAG_THRESHOLD', 0.30);
            $topK      = (int)   env('RAG_TOP_K', 5);
            $scored    = [];

            foreach ($docs as $chunk) {
                $path    = $chunk['path']    ?? '';
                $content = $chunk['content'] ?? '';
                $vec     = $chunk['vector']  ?? [];
                if (empty($content) || empty($vec)) continue;
                if (in_array(basename($path), self::EXCLUDED_PATHS, true)) continue;
                $score = $this->cosineSimilarity($vector, $vec);
                if ($score >= $threshold) {
                    $scored[] = ['score' => $score, 'content' => $content, 'path' => $path];
                }
            }

            if (empty($scored)) {
                return ['context' => '', 'error' => "No chunks above threshold ({$threshold}). Try lowering RAG_THRESHOLD."];
            }

            usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
            $top = array_slice($scored, 0, $topK);

            $context = implode("\n\n---\n\n", array_map(
                fn($c) => "Source: {$c['path']}\n\n{$c['content']}",
                $top
            ));

            return ['context' => $context, 'error' => null];
        } catch (\Throwable $e) {
            return ['context' => '', 'error' => $e->getMessage()];
        }
    }

    private function buildAgent(string $context): Agent
    {
        $ragBlock = $context !== ''
            ? "\n\n════════════════════════════════════\nDOCUMENTATION CONTEXT\n════════════════════════════════════\n{$context}\n════════════════════════════════════\nAnswer using the documentation above when relevant.\n"
            : '';

        $system = <<<PROMPT
You are an AI assistant for the Doppar PHP Framework (created by Mahedi Hasan).
{$ragBlock}
RESPONSIBILITIES:
- Provide accurate technical help about Doppar Framework
- Give clear, practical PHP code examples using Doppar syntax only
- Never use Laravel, Symfony, or other framework syntax
- If the documentation above covers the question, answer from it directly
- If not covered, answer from general Doppar knowledge and say so
- End every response with a short follow-up question
PROMPT;

        return Agent::using(OpenAI::class)
            ->withKey(env('OPENAI_API_KEY'))
            ->model(env('OPENAI_MODEL', 'gpt-4o-mini'))
            ->withStore(new CacheStore($this->storePath))
            ->system($system);
    }

    #[Route(uri: '/chat', name: 'ai.chat', methods: ['POST'])]
    public function chat(Request $request)
    {
        $message = trim((string) ($request->message ?? ''));
        if ($message === '') {
            return response()->json(['success' => false, 'error' => 'Message is required'], 422);
        }

        $key   = $this->conversationKey($request);
        $store = new CacheStore($this->storePath);
        $rag   = $this->generateContext($message);

        $agent = $this->buildAgent($rag['context']);

        // Load existing conversation history for this chat_id
        if ($store->has($key)) {
            $agent->loadMessages($key);
        }

        return response()->stream(
            function () use ($agent, $key, $message, $rag) {
                $full = '';
                try {
                    // Surface RAG errors visibly during development
                    if ($rag['error'] !== null) {
                        $warn = "⚠ RAG unavailable: {$rag['error']}\n\n";
                        echo 'data: ' . json_encode(['chunk' => $warn]) . "\n\n";
                        flush();
                        $full .= $warn;
                    }

                    $stream = $agent->prompt($message)->stream();

                    foreach ($stream as $chunk) {
                        if (!$chunk) continue;
                        $full .= $chunk;
                        echo 'data: ' . json_encode(
                            ['chunk' => $chunk],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ) . "\n\n";
                        if (ob_get_level() > 0) ob_flush();
                        flush();
                    }

                    // Persist via agent->store() which handles the full exchange
                    $agent->store($key, $full);

                    echo 'data: ' . json_encode(['done' => true]) . "\n\n";
                } catch (\Throwable $e) {
                    echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
                }
                if (ob_get_level() > 0) ob_flush();
                flush();
            },
            200,
            [
                'Content-Type'      => 'text/event-stream; charset=UTF-8',
                'Cache-Control'     => 'no-cache, no-store, must-revalidate',
                'X-Accel-Buffering' => 'no',
                'Connection'        => 'keep-alive',
            ]
        );
    }

    #[Route(uri: 'history', name: 'ai.history', methods: ['GET'])]
    public function history(Request $request)
    {
        $key      = $this->conversationKey($request);
        $store    = new CacheStore($this->storePath);
        $messages = [];

        if ($store->has($key)) {
            $raw = $store->load($key);

            // Handle different storage formats
            if (is_string($raw)) {
                $unserialized = @unserialize($raw);
                if ($unserialized !== false && is_array($unserialized)) {
                    $raw = $unserialized;
                } else {
                    $raw = [];
                }
            }

            if (is_array($raw)) {
                $messages = array_values(
                    array_filter($raw, fn($m) => ($m['role'] ?? '') !== 'system')
                );
            }
        }

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    #[Route(uri: 'clear-history', name: 'ai.clear-history', methods: ['POST'])]
    public function clearHistory(Request $request)
    {
        $key   = $this->conversationKey($request);
        $store = new CacheStore($this->storePath);
        if ($store->has($key)) {
            $store->delete($key);
        }
        return response()->json(['success' => true]);
    }

    #[Route(uri: 'rag-debug', name: 'ai.rag-debug', methods: ['GET'])]
    public function ragDebug(Request $request)
    {
        $q    = $request->q ?? 'How do I create a model in Doppar?';
        $docs = $this->loadDocs();
        $rag  = $this->generateContext($q);
        $paths = array_unique(array_column($docs, 'path'));
        sort($paths);
        return response()->json([
            'vector_file'   => $this->vectorFile,
            'file_exists'   => file_exists($this->vectorFile),
            'chunk_count'   => count($docs),
            'all_paths'     => $paths,
            'threshold'     => (float) env('RAG_THRESHOLD', 0.30),
            'top_k'         => (int)   env('RAG_TOP_K', 5),
            'question'      => $q,
            'rag_error'     => $rag['error'],
            'rag_working'   => $rag['error'] === null && $rag['context'] !== '',
            'context_chars' => strlen($rag['context']),
            'preview'       => $rag['context'] ? mb_substr($rag['context'], 0, 600) . '…' : null,
        ]);
    }

    #[Route(uri: 'status', name: 'ai.status', methods: ['GET'])]
    public function status()
    {
        $docs = $this->loadDocs();
        return response()->json([
            'success'       => true,
            'model'         => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'embedding'     => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'rag'           => file_exists($this->vectorFile),
            'vector_chunks' => count($docs),
        ]);
    }
}
