<?php

namespace App\Http\Controllers;

use Phaseolies\Utilities\Attributes\Route;
use Phaseolies\Utilities\Attributes\Mapper;
use Phaseolies\Http\Request;
use Doppar\AI\AgentFactory\Agent\OpenAI;
use Doppar\AI\Store\CacheStore;
use Doppar\AI\Vector\Vector;
use Doppar\AI\Agent;
use App\Http\Controllers\Controller;

#[Mapper(prefix: 'ai', middleware: ['throttle:60,1'])]
class WelcomeController extends Controller
{
    private string $storeBasePath;

    public function __construct()
    {
        $this->storeBasePath = storage_path('doppar_ai_conversations');
    }

    private function loadVectorDocs(): array
    {
        static $docs = null;
        if ($docs !== null) {
            return $docs;
        }
        $path = public_path('vector_docs_cache.json');
        if (!file_exists($path)) {
            $docs = [];
            return $docs;
        }
        $decoded = json_decode(file_get_contents($path), true);
        $docs    = is_array($decoded) ? $decoded : [];
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
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }
        if ($normA === 0.0 || $normB === 0.0) return 0.0;
        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function getVectorContext(string $question): array
    {
        $docs = $this->loadVectorDocs();

        if (empty($docs)) {
            return [
                'context' => '',
                'error'   => 'vector_docs_cache.json not found or empty at: '
                    . public_path('vector_docs_cache.json'),
            ];
        }

        $first = $docs[0] ?? [];
        if (!isset($first['vector']) || !isset($first['content'])) {
            return [
                'context' => '',
                'error'   => 'Chunks missing required keys. Found: ['
                    . implode(', ', array_keys($first))
                    . ']. Expected: [vector, content]',
            ];
        }

        try {
            $embedAgent = Agent::using(OpenAI::class)
                ->withKey(env('OPENAI_API_KEY'));

            $questionVector = Vector::embedding(
                $embedAgent,
                env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
                $question
            );

            if (empty($questionVector)) {
                return ['context' => '', 'error' => 'Vector::embedding() returned empty result.'];
            }

            $topK      = (int) env('RAG_TOP_K', 5);
            $threshold = (float) env('RAG_THRESHOLD', 0.30);
            $scored    = [];

            foreach ($docs as $chunk) {
                $path    = $chunk['path'] ?? '';
                $content = $chunk['content'] ?? '';
                $vector  = $chunk['vector'] ?? [];

                $basename = basename($path);
                if (in_array($basename, self::EXCLUDED_PATHS, true)) {
                    continue;
                }

                if (empty($content) || empty($vector)) {
                    continue;
                }

                $score = $this->cosineSimilarity($questionVector, $vector);

                if ($score >= $threshold) {
                    $scored[] = ['score' => $score, 'content' => $content, 'path' => $path];
                }
            }

            if (empty($scored)) {
                return [
                    'context' => '',
                    'error'   => "No chunks met the similarity threshold ({$threshold}). "
                        . 'Try lowering RAG_THRESHOLD in .env.',
                ];
            }

            usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
            $topChunks = array_slice($scored, 0, $topK);

            $context = implode("\n\n---\n\n", array_map(
                fn($c) => "Source: {$c['path']}\n\n{$c['content']}",
                $topChunks
            ));

            return ['context' => $context, 'error' => null];
        } catch (\Throwable $e) {
            return ['context' => '', 'error' => 'Embedding failed: ' . $e->getMessage()];
        }
    }

    private function makeChatAgent(string $context = ''): Agent
    {
        return Agent::using(OpenAI::class)
            ->withKey(env('OPENAI_API_KEY'))
            ->model(env('OPENAI_MODEL', 'gpt-4o-mini'))
            ->withStore(new CacheStore($this->storeBasePath))
            ->system($this->buildSystemPrompt($context));
    }

    private function conversationKey(string $sessionId): string
    {
        return 'doppar-chat-' . md5($sessionId);
    }

    #[Route(uri: '/chat', name: 'ai.chat', methods: ['POST'])]
    public function chat(Request $request)
    {
        $userMessage = trim((string) ($request->message ?? ''));

        if ($userMessage === '') {
            return response()->json(['success' => false, 'error' => 'Message cannot be empty.'], 422);
        }

        $sessionId = $request->session()->getId() ?? session_id() ?: 'guest';
        $key       = $this->conversationKey($sessionId);
        $store     = new CacheStore($this->storeBasePath);

        $rag     = $this->getVectorContext($userMessage);
        $context = $rag['context'];
        $ragErr  = $rag['error'];

        $agent = $this->makeChatAgent($context);

        if ($store->has($key)) {
            $agent->loadMessages($key);
        }

        return response()->stream(
            function () use ($agent, $key, $userMessage, $ragErr) {
                $fullResponse = '';
                try {

                    if ($ragErr !== null) {
                        $warn = "⚠️ RAG unavailable ({$ragErr}) — answering from training knowledge.\n\n";
                        echo 'data: ' . json_encode(['chunk' => $warn]) . "\n\n";
                        flush();
                        $fullResponse .= $warn;
                    }

                    $stream = $agent->prompt($userMessage)->stream();

                    foreach ($stream as $chunk) {
                        if ($chunk === null || $chunk === '') continue;
                        $fullResponse .= $chunk;
                        echo 'data: ' . json_encode(
                            ['chunk' => $chunk],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ) . "\n\n";
                        if (ob_get_level() > 0) ob_flush();
                        flush();
                    }

                    $agent->store($key, $fullResponse);
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

    #[Route(uri: 'clear-history', name: 'ai.clear-history', methods: ['POST'])]
    public function clearHistory(Request $request)
    {
        $sessionId = $request->session()->getId() ?? session_id();
        if ($sessionId) {
            $store = new CacheStore($this->storeBasePath);
            $key   = $this->conversationKey($sessionId);
            if ($store->has($key)) $store->delete($key);
        }
        return response()->json(['success' => true, 'message' => 'Conversation history cleared.']);
    }

    #[Route(uri: 'history', name: 'ai.history', methods: ['GET'])]
    public function history(Request $request)
    {
        $sessionId = $request->session()->getId() ?? session_id();
        $messages  = [];
        if ($sessionId) {
            $store = new CacheStore($this->storeBasePath);
            $key   = $this->conversationKey($sessionId);
            if ($store->has($key)) {
                $raw      = $store->load($key) ?? [];
                $messages = array_values(
                    array_filter($raw, fn($m) => ($m['role'] ?? '') !== 'system')
                );
            }
        }
        return response()->json(['success' => true, 'messages' => $messages]);
    }

    #[Route(uri: 'rag-debug', name: 'ai.rag-debug', methods: ['GET'])]
    public function ragDebug(Request $request)
    {
        $question = $request->q ?? 'How do I create a model in Doppar?';
        $docs     = $this->loadVectorDocs();
        $rag      = $this->getVectorContext($question);

        $paths = array_unique(array_column($docs, 'path'));
        sort($paths);

        return response()->json([
            'vector_docs_path'   => public_path('vector_docs_cache.json'),
            'vector_docs_exists' => file_exists(public_path('vector_docs_cache.json')),
            'vector_chunk_count' => count($docs),
            'all_paths'          => $paths,
            'excluded_paths'     => self::EXCLUDED_PATHS,
            'embedding_model'    => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'chat_model'         => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'rag_threshold'      => (float) env('RAG_THRESHOLD', 0.30),
            'rag_top_k'          => (int) env('RAG_TOP_K', 5),
            'question_tested'    => $question,
            'rag_error'          => $rag['error'],
            'rag_working'        => $rag['error'] === null && $rag['context'] !== '',
            'context_chars'      => strlen($rag['context']),
            'context_preview'    => $rag['context'] !== ''
                ? mb_substr($rag['context'], 0, 600) . '…'
                : null,
        ]);
    }

    #[Route(uri: 'status', name: 'ai.status', methods: ['GET'])]
    public function status()
    {
        $docs = $this->loadVectorDocs();
        return response()->json([
            'success'       => true,
            'status'        => 'operational',
            'model'         => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'embedding'     => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'streaming'     => true,
            'persistence'   => true,
            'rag'           => true,
            'vector_docs'   => file_exists(public_path('vector_docs_cache.json')),
            'vector_chunks' => count($docs),
        ]);
    }

    private function buildSystemPrompt(string $context = ''): string
    {
        if ($context !== '') {
            // RAG is working — strict mode: only answer from the docs
            return <<<PROMPT
You are a documentation assistant for the Doppar PHP Framework. Answer only using the documentation provided using vector embedding data. If a user asks a question that does not have a direct answer in the documentation (for example, how to create a CRUD application in Doppar), generate the answer based on the knowledge you have derived from the provided RAG content or your general knowledge from different sources like blogs etc.

════════════════════════════════════════════
DOPPAR DOCUMENTATION
════════════════════════════════════════════
{$context}
════════════════════════════════════════════

Rules:
- Quote or paraphrase directly from the documentation above
- Always include working PHP code examples when the docs show them
- Be concise and precise
- End every response with a short follow-up question
PROMPT;
        }

        // RAG unavailable — answer from built-in knowledge, but be honest about it
        return <<<'PROMPT'
You are an expert AI assistant for the Doppar PHP Framework.
Note: Documentation context is currently unavailable, answering from general knowledge.

**Core Doppar Framework:**
- Modern PHP framework created by Mahedi Hasan
- Built for robust, scalable web applications
- Features: Middleware support, Repository pattern, Attribute-based routing, Facade support

**Doppar AI Component (doppar/ai):**
- Built on Symfony AI and Transformers.php
- Pipeline: Run 15+ transformer tasks locally (sentiment analysis, text generation, translation, QA, image classification, object detection, ASR, etc.)
- Agent: Fluent interface for cloud LLMs — OpenAI, Gemini, Claude, OpenRouter, SelfHost
- Streaming: withStreaming() / stream() methods return a PHP Generator for token-by-token output
- Agent Persistence: StoreInterface + CacheStore for saving/loading multi-turn conversation history
- Vector Helper: cosine similarity, context retrieval, embeddings for RAG workflows
- Rate Limiting: throttle() helper for protecting agent endpoints

**HTTP Responses in Doppar:**
- response()->json(), response()->stream(), response()->streamJson()
- response()->download(), response()->file(), response()->streamDownload()
- Redirect helpers: redirect()->route(), redirect()->back(), redirect()->away()
- Caching: setPublic(), setPrivate(), setMaxAge(), setSharedMaxAge()

**Behavior:**
- Be concise, practical, and precise
- Use Doppar syntax — never Laravel or Symfony syntax
- Admit when you don't know something specific to Doppar
- Keep responses under 500 words unless a detailed walkthrough is needed
- End every response with a short follow-up question
PROMPT;
    }
}
