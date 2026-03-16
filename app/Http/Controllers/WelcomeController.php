<?php

namespace App\Http\Controllers;

use Phaseolies\Utilities\Attributes\Route;
use Phaseolies\Utilities\Attributes\Mapper;
use Phaseolies\Http\Request;
use Doppar\AI\Agent;
use Doppar\AI\Vector\Vector;
use Doppar\AI\Store\CacheStore;
use Doppar\AI\AgentFactory\Agent\OpenAI;

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

    /*
    |--------------------------------------------------------------------------
    | Load Vector Docs
    |--------------------------------------------------------------------------
    */

    private function loadDocs(): array
    {
        if (!file_exists($this->vectorFile)) {
            return [];
        }

        $docs = json_decode(file_get_contents($this->vectorFile), true);

        return is_array($docs) ? $docs : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Build Agent
    |--------------------------------------------------------------------------
    */

    private function buildAgent(string $context): Agent
    {
        return Agent::using(OpenAI::class)
            ->withKey(env('OPENAI_API_KEY'))
            ->model(env('OPENAI_MODEL', 'gpt-4o-mini'))
            ->withStore(new CacheStore($this->storePath))
            ->system(
            "You are the official AI assistant for the Doppar PHP Framework which is created by Mahedi Hasan.

Priority: Answer using the provided documentation context with clear examples and explanations. If the answer is not present in the documentation, then provide an answer from internet sources like blogs or the doppar.com documentation site. always ends response with follow-up questions.\"

Documentation context:

{$context}"
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Context (RAG)
    |--------------------------------------------------------------------------
    */

    private function generateContext(string $question): array
    {
        $docs = $this->loadDocs();

        if (!$docs) {
            return [
                'context' => '',
                'error' => 'Vector documentation cache not found'
            ];
        }

        try {

            $agent = Agent::using(OpenAI::class)
                ->withKey(env('OPENAI_API_KEY'));

            $vector = Vector::embedding(
                $agent,
                env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
                $question
            );

            $context = Vector::getContext($docs, $vector);

            return [
                'context' => $context,
                'error' => null
            ];
        } catch (\Throwable $e) {

            return [
                'context' => '',
                'error' => $e->getMessage()
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Conversation Key
    |--------------------------------------------------------------------------
    */

    private function conversationKey(string $session): string
    {
        return 'doppar-ai-' . md5($session);
    }

    /*
    |--------------------------------------------------------------------------
    | Chat Endpoint (Streaming)
    |--------------------------------------------------------------------------
    */

    #[Route(uri: '/chat', methods: ['POST'])]
    public function chat(Request $request)
    {
        $message = trim((string)$request->message);

        if (!$message) {
            return response()->json([
                'success' => false,
                'error' => 'Message is required'
            ], 422);
        }

        $session = $request->session()->getId() ?? session_id() ?? 'guest';

        $conversationKey = $this->conversationKey($session);

        $store = new CacheStore($this->storePath);

        $rag = $this->generateContext($message);

        $agent = $this->buildAgent($rag['context']);

        if ($store->has($conversationKey)) {
            $agent->loadMessages($conversationKey);
        }

        return response()->stream(function () use ($agent, $message, $conversationKey, $rag) {

            $full = '';

            try {

                if ($rag['error']) {

                    $warn = "⚠ RAG unavailable: {$rag['error']}\n\n";

                    echo 'data: ' . json_encode(['chunk' => $warn]) . "\n\n";

                    flush();

                    $full .= $warn;
                }

                $stream = $agent
                    ->prompt("Context:\n{$rag['context']}\n\nQuestion: {$message}")
                    ->stream();

                foreach ($stream as $chunk) {

                    if (!$chunk) {
                        continue;
                    }

                    $full .= $chunk;

                    echo 'data: ' . json_encode([
                        'chunk' => $chunk
                    ], JSON_UNESCAPED_UNICODE) . "\n\n";

                    if (ob_get_level()) {
                        ob_flush();
                    }

                    flush();
                }

                $agent->store($conversationKey, $full);

                echo 'data: ' . json_encode(['done' => true]) . "\n\n";
            } catch (\Throwable $e) {

                echo 'data: ' . json_encode([
                    'error' => $e->getMessage()
                ]) . "\n\n";
            }

            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Chat History
    |--------------------------------------------------------------------------
    */

    #[Route(uri: 'history', methods: ['GET'])]
    public function history(Request $request)
    {
        $session = $request->session()->getId() ?? session_id();

        $store = new CacheStore($this->storePath);

        $key = $this->conversationKey($session);

        $messages = [];

        if ($store->has($key)) {

            $raw = $store->load($key) ?? [];

            $messages = array_values(
                array_filter($raw, fn($m) => ($m['role'] ?? '') !== 'system')
            );
        }

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Clear Chat History
    |--------------------------------------------------------------------------
    */

    #[Route(uri: 'clear-history', methods: ['POST'])]
    public function clearHistory(Request $request)
    {
        $session = $request->session()->getId() ?? session_id();

        $store = new CacheStore($this->storePath);

        $key = $this->conversationKey($session);

        if ($store->has($key)) {
            $store->delete($key);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
