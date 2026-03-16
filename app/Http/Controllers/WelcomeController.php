<?php

namespace App\Http\Controllers;

use Phaseolies\Utilities\Attributes\Route;
use Phaseolies\Utilities\Attributes\Mapper;
use Phaseolies\Http\Request;
use Doppar\AI\AgentFactory\Agent\OpenAI;
use Doppar\AI\Store\CacheStore;
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

    private function makeAgent(): Agent
    {
        return Agent::using(OpenAI::class)
            ->withKey(env('OPENAI_API_KEY'))
            ->model(env('OPENAI_MODEL', 'gpt-4o-mini'))
            ->withStore(new CacheStore($this->storeBasePath))
            ->system($this->getSystemPrompt());
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
            return response()->json([
                'success' => false,
                'error'   => 'Message cannot be empty.',
            ], 422);
        }

        $rateLimitKey = 'ai-chat:' . ($request->ip() ?? 'unknown');
        if (throttle()->tooManyAttempts($rateLimitKey, 20)) {
            $wait = throttle()->availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'error'   => "Too many requests. Please wait {$wait}s and try again.",
            ], 429);
        }
        throttle()->hit($rateLimitKey, 60);

        $sessionId = $request->session()->getId() ?? session_id() ?: 'guest';
        $key       = $this->conversationKey($sessionId);
        $store     = new CacheStore($this->storeBasePath);
        $agent     = $this->makeAgent();

        if ($store->has($key)) {
            $agent->loadMessages($key);
        }

        return response()->stream(
            function () use ($agent, $key, $userMessage) {
                $fullResponse = '';

                try {
                    $stream = $agent->prompt($userMessage)->stream();

                    foreach ($stream as $chunk) {
                        if ($chunk === null || $chunk === '') {
                            continue;
                        }

                        $fullResponse .= $chunk;

                        echo 'data: ' . json_encode(
                            ['chunk' => $chunk],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ) . "\n\n";

                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }

                    $agent->store($key, $fullResponse);

                    echo 'data: ' . json_encode(['done' => true]) . "\n\n";
                } catch (\Throwable $e) {
                    echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
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
            if ($store->has($key)) {
                $store->delete($key);
            }
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

    #[Route(uri: 'status', name: 'ai.status', methods: ['GET'])]
    public function status()
    {
        return response()->json([
            'success'     => true,
            'status'      => 'operational',
            'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'streaming'   => true,
            'persistence' => true,
        ]);
    }

    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert AI assistant specializing in the Doppar PHP Framework.

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
- Quantized models for local performance

**HTTP Responses in Doppar:**
- response()->json(), response()->stream(), response()->streamJson()
- response()->download(), response()->file(), response()->streamDownload()
- Redirect helpers: redirect()->route(), redirect()->back(), redirect()->away()
- Caching: setPublic(), setPrivate(), setMaxAge(), setSharedMaxAge()
- Status helpers: isOk(), isSuccessful(), isNotFound(), etc.

**Behavior:**
- Be concise, practical, and precise
- Always provide working PHP code examples
- Use Doppar/PHP syntax exclusively in code blocks
- Admit when you don't know something
- Keep responses under 500 words unless a detailed walkthrough is needed
- End every response with a short follow-up question
PROMPT;
    }
}
