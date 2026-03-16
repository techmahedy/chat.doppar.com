<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Doppar AI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="[[ csrf_token() ]]">

    <!-- Tailwind Play CDN (dev only — swap for compiled CSS in production) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"DM Mono"', 'monospace'],
                        display: ['"Instrument Serif"', 'serif'],
                    },
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* ── Tokens ────────────────────────────────────────────────────── */
        :root {
            --c-bg: #f7f5f2;
            --c-card: #ffffff;
            --c-border: #e8e4de;
            --c-ink: #1a1a18;
            --c-muted: #8c8880;
            --c-accent: #1a6ef5;
            --c-accent-l: #e8f0fe;
            --c-code-bg: #181c28;
            --c-code-fg: #c8d3ed;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            background: var(--c-bg);
            font-family: 'DM Sans', system-ui, sans-serif;
            color: var(--c-ink);
        }

        /* ── Scrollbar ─────────────────────────────────────────────────── */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: var(--c-border);
            border-radius: 2px;
        }

        /* ── Code inside bubbles ───────────────────────────────────────── */
        .msg-bubble pre {
            background: var(--c-code-bg);
            color: var(--c-code-fg);
            padding: 12px 14px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'DM Mono', monospace;
            font-size: 0.775rem;
            line-height: 1.6;
            margin: 8px 0;
            white-space: pre;
        }

        .msg-bubble code {
            font-family: 'DM Mono', monospace;
            font-size: 0.8rem;
            background: rgba(26, 110, 245, .08);
            color: var(--c-accent);
            padding: 1px 5px;
            border-radius: 4px;
        }

        .msg-bubble strong {
            font-weight: 600;
        }

        .user-bubble code {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        /* ── Streaming cursor ──────────────────────────────────────────── */
        .cursor-blink::after {
            content: '▋';
            animation: blink .6s step-end infinite;
            color: var(--c-accent);
            font-size: .85em;
            margin-left: 1px;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: 0
            }
        }

        /* ── Typing dots ───────────────────────────────────────────────── */
        @keyframes dot {

            0%,
            60%,
            100% {
                transform: translateY(0);
                opacity: .4
            }

            30% {
                transform: translateY(-5px);
                opacity: 1
            }
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--c-muted);
            display: inline-block;
        }

        .typing-dot:nth-child(1) {
            animation: dot 1.1s infinite;
        }

        .typing-dot:nth-child(2) {
            animation: dot 1.1s .18s infinite;
        }

        .typing-dot:nth-child(3) {
            animation: dot 1.1s .36s infinite;
        }

        /* ── Fade animations ───────────────────────────────────────────── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        .anim-fade-up {
            animation: fadeUp .35s ease forwards;
        }

        .anim-fade-in {
            animation: fadeIn .2s ease forwards;
        }

        /* ── Views ─────────────────────────────────────────────────────── */
        #homeView {
            display: flex;
        }

        #chatView {
            display: none;
        }

        #chatView.active {
            display: flex;
        }

        /* ── Category tabs ─────────────────────────────────────────────── */
        .cat-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .75rem;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 9999px;
            border: 1px solid var(--c-border);
            background: var(--c-card);
            color: var(--c-muted);
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }

        .cat-btn:hover {
            background: #f0ede8;
            color: var(--c-ink);
        }

        .cat-btn.active {
            background: var(--c-ink);
            color: #fff;
            border-color: var(--c-ink);
        }

        /* ── Suggestion rows ───────────────────────────────────────────── */
        .sugg-row {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            text-align: left;
            padding: 11px 16px;
            font-size: .84rem;
            color: var(--c-ink);
            background: none;
            border: none;
            cursor: pointer;
            transition: background .12s;
        }

        .sugg-row:hover {
            background: #f0ede8;
        }

        .sugg-row+.sugg-row {
            border-top: 1px solid var(--c-border);
        }

        /* ── Search / input box ────────────────────────────────────────── */
        .input-box {
            background: var(--c-card);
            border: 1px solid var(--c-border);
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            overflow: hidden;
            transition: box-shadow .2s, border-color .2s;
        }

        .input-box:focus-within {
            border-color: #b0c8fd;
            box-shadow: 0 0 0 3px rgba(26, 110, 245, .1);
        }

        .input-box textarea {
            display: block;
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            resize: none;
            font-family: 'DM Sans', sans-serif;
            font-size: .875rem;
            color: var(--c-ink);
            line-height: 1.5;
            min-height: 26px;
            max-height: 120px;
            overflow-y: auto;
            padding: 14px 16px 0;
        }

        .input-box textarea::placeholder {
            color: var(--c-muted);
        }

        .input-box .input-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px 10px;
        }

        /* ── Send button ───────────────────────────────────────────────── */
        .send-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--c-ink);
            border: none;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            transition: background .15s, transform .1s;
            flex-shrink: 0;
        }

        .send-btn:hover:not(:disabled) {
            background: var(--c-accent);
            transform: scale(1.04);
        }

        .send-btn:disabled {
            background: var(--c-border);
            color: var(--c-muted);
            cursor: not-allowed;
        }

        /* ── Model badge ───────────────────────────────────────────────── */
        .model-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            color: var(--c-muted);
            background: var(--c-bg);
            border: 1px solid var(--c-border);
            border-radius: 9999px;
            padding: 3px 10px;
        }

        /* ── Sidebar icons ─────────────────────────────────────────────── */
        .side-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--c-muted);
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        .side-btn:hover {
            background: var(--c-bg);
            color: var(--c-ink);
        }

        /* ── Message bubbles ───────────────────────────────────────────── */
        .bubble-agent {
            background: var(--c-card);
            border: 1px solid var(--c-border);
            border-radius: 16px;
            border-bottom-left-radius: 3px;
            padding: 10px 14px;
            font-size: .875rem;
            line-height: 1.65;
            color: var(--c-ink);
        }

        .bubble-user {
            background: var(--c-accent);
            border-radius: 16px;
            border-bottom-right-radius: 3px;
            padding: 10px 14px;
            font-size: .875rem;
            line-height: 1.65;
            color: #fff;
        }

        /* ── Status ────────────────────────────────────────────────────── */
        @keyframes pulseGreen {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .3
            }
        }

        .status-streaming {
            animation: pulseGreen .7s infinite;
        }
    </style>
</head>

<body>

    <!-- ═══════════════════════════════════════════════════════════════════════════
     OUTER SHELL  — sidebar + centered main column
════════════════════════════════════════════════════════════════════════════ -->
    <div style="display:flex;height:100vh;overflow:hidden;">

        <!-- Sidebar -->
        <aside style="width:52px;background:var(--c-card);border-right:1px solid var(--c-border);display:flex;flex-direction:column;align-items:center;padding:12px 0;gap:6px;flex-shrink:0;">
            <!-- Logo -->
            <div style="width:30px;height:30px;border-radius:8px;background:var(--c-accent);display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;margin-bottom:8px;">D</div>
            <button class="side-btn" title="Home" onclick="goHome()"><i class="bi bi-house" style="font-size:15px;"></i></button>
            <button class="side-btn" title="New Chat" onclick="newChat()"><i class="bi bi-plus-lg" style="font-size:15px;"></i></button>
            <button class="side-btn" title="History"><i class="bi bi-clock-history" style="font-size:14px;"></i></button>
            <div style="flex:1;"></div>
            <button class="side-btn" title="Settings"><i class="bi bi-gear" style="font-size:14px;"></i></button>
        </aside>

        <!-- ── CENTER COLUMN (max-width container) ── -->
        <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;">

            <!-- ════════════ HOME VIEW ════════════ -->
            <div id="homeView" style="flex:1;flex-direction:column;align-items:center;justify-content:center;padding:32px 20px;overflow-y:auto;" class="scrollbar-thin">
                <div style="width:100%;max-width:660px;" class="anim-fade-up">

                    <!-- Brand -->
                    <div style="text-align:center;margin-bottom:28px;">
                        <h1 style="font-family:'Instrument Serif',serif;font-size:2.4rem;font-weight:400;letter-spacing:-.02em;color:var(--c-ink);">
                            doppar <em style="font-style:italic;color:var(--c-accent);">ai</em>
                        </h1>
                        <p style="font-size:.82rem;color:var(--c-muted);margin-top:4px;">Framework assistant · powered by doppar/ai</p>
                    </div>

                    <!-- Search box -->
                    <div class="input-box">
                        <textarea id="homeInput" rows="1" placeholder="Ask anything about Doppar Framework…"
                            oninput="autoGrow(this)" onkeydown="homeKeydown(event)"></textarea>
                        <div class="input-footer">
                            <div class="model-badge"><i class="bi bi-cpu" style="color:var(--c-accent);font-size:11px;"></i><span id="modelLabel">gpt-4o-mini</span></div>
                            <button class="send-btn" id="homeSendBtn" onclick="sendFromHome()"><i class="bi bi-arrow-up"></i></button>
                        </div>
                    </div>

                    <!-- Category tabs -->
                    <div style="display:flex;flex-wrap:wrap;gap:7px;margin-top:16px;">
                        <button class="cat-btn active" data-cat="framework" onclick="selectCat(this,'framework')"><i class="bi bi-layers" style="font-size:11px;"></i>Framework</button>
                        <button class="cat-btn" data-cat="pipeline" onclick="selectCat(this,'pipeline')"><i class="bi bi-diagram-3" style="font-size:11px;"></i>Pipeline</button>
                        <button class="cat-btn" data-cat="agents" onclick="selectCat(this,'agents')"><i class="bi bi-robot" style="font-size:11px;"></i>Agents</button>
                        <button class="cat-btn" data-cat="streaming" onclick="selectCat(this,'streaming')"><i class="bi bi-broadcast" style="font-size:11px;"></i>Streaming</button>
                        <button class="cat-btn" data-cat="persistence" onclick="selectCat(this,'persistence')"><i class="bi bi-database" style="font-size:11px;"></i>Persistence</button>
                        <button class="cat-btn" data-cat="responses" onclick="selectCat(this,'responses')"><i class="bi bi-send" style="font-size:11px;"></i>Responses</button>
                    </div>

                    <!-- Suggestion list -->
                    <div id="suggBlock" style="margin-top:10px;background:var(--c-card);border:1px solid var(--c-border);border-radius:12px;overflow:hidden;"></div>

                </div>
            </div>

            <!-- ════════════ CHAT VIEW ════════════ -->
            <div id="chatView" style="flex:1;flex-direction:column;overflow:hidden;" class="scrollbar-thin">

                <!-- Top bar -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-bottom:1px solid var(--c-border);background:var(--c-card);flex-shrink:0;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:26px;height:26px;border-radius:7px;background:var(--c-accent);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;">D</div>
                        <span style="font-size:.875rem;font-weight:600;">Doppar Assistant</span>
                        <div id="chatStatus" style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--c-muted);">
                            <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span>Online
                        </div>
                    </div>
                    <div style="display:flex;gap:4px;">
                        <button class="side-btn" title="Clear chat" onclick="clearHistory()" style="width:30px;height:30px;color:var(--c-muted);" onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2'" onmouseout="this.style.color='var(--c-muted)';this.style.background='none'">
                            <i class="bi bi-trash3" style="font-size:13px;"></i>
                        </button>
                        <button class="side-btn" title="Home" onclick="goHome()" style="width:30px;height:30px;">
                            <i class="bi bi-house" style="font-size:13px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages — centered container -->
                <div id="chatMessages" class="scrollbar-thin" style="flex:1;overflow-y:auto;padding:24px 20px;">
                    <div style="max-width:680px;margin:0 auto;display:flex;flex-direction:column;gap:20px;" id="msgInner"></div>
                </div>

                <!-- Input bar — centered container -->
                <div style="padding:12px 20px 16px;flex-shrink:0;background:var(--c-bg);">
                    <div style="max-width:680px;margin:0 auto;">
                        <div class="input-box">
                            <textarea id="chatInput" rows="1" placeholder="Ask a follow-up…"
                                oninput="autoGrow(this)" onkeydown="chatKeydown(event)"></textarea>
                            <div class="input-footer">
                                <div class="model-badge"><i class="bi bi-cpu" style="color:var(--c-accent);font-size:11px;"></i>gpt-4o-mini</div>
                                <button class="send-btn" id="chatSendBtn" onclick="sendFromChat()"><i class="bi bi-arrow-up"></i></button>
                            </div>
                        </div>
                        <p style="text-align:center;font-size:.68rem;color:var(--c-muted);margin-top:6px;">Powered by Doppar AI · doppar/ai package</p>
                    </div>
                </div>

            </div>

        </div><!-- /center column -->
    </div><!-- /outer shell -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        'use strict';

        // ─── Utils ────────────────────────────────────────────────────────────────────
        const $ = id => document.getElementById(id);
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const now = () => new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
        const rmEl = n => n?.parentNode?.removeChild(n);

        function autoGrow(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        }

        // ─── Suggestions data ─────────────────────────────────────────────────────────
        const SUGG = {
            framework: [
                'What is Doppar Framework and who created it?',
                'How does attribute-based routing work in Doppar?',
                'How do I register and use Middleware in Doppar?',
                'What is the Repository pattern and how to use it?',
                'How do I install and configure Doppar from scratch?',
            ],
            pipeline: [
                'What Pipeline tasks are available in doppar/ai?',
                'How do I run sentiment analysis with Pipeline?',
                'Show me a translation example using Pipeline',
                'How do I do zero-shot image classification?',
                'How does Automatic Speech Recognition (ASR) work?',
            ],
            agents: [
                'How do I create an OpenAI Agent in Doppar?',
                'How do I use Google Gemini as an LLM agent?',
                'How do I connect Claude Anthropic with doppar/ai?',
                'How do I run a self-hosted model with SelfHost?',
                'How do I set a system prompt for an Agent?',
            ],
            streaming: [
                'How does token streaming work with doppar/ai?',
                'What is the difference between stream() and withStreaming()?',
                'Show me a full PHP SSE streaming controller example',
                'How do I stream with system messages and parameters?',
                'How do I consume SSE in the browser with fetch?',
            ],
            persistence: [
                'What is Agent Persistence in doppar/ai?',
                'How do I save and restore a chat with CacheStore?',
                'How do I build a stateful multi-turn conversation?',
                'How do I implement a custom DatabaseStore?',
                'How do I scope chats per user using auth()->id()?',
            ],
            responses: [
                'How do I return a JSON response in Doppar?',
                'How does response()->stream() work step by step?',
                'How do I trigger a file download in Doppar?',
                'How do I redirect to a named route with parameters?',
                'How do I set Cache-Control headers on a response?',
            ],
        };

        let currentCat = 'framework';

        function selectCat(btn, cat) {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCat = cat;
            renderSugg(cat);
        }

        function renderSugg(cat) {
            const block = $('suggBlock');
            const items = SUGG[cat] || [];
            // Use data-idx attribute — avoids quote-escaping hell in onclick
            block.innerHTML = items.map((q, i) => `
        <button class="sugg-row" data-idx="${i}" data-cat="${cat}">
            <i class="bi bi-arrow-up-right" style="color:var(--c-muted);font-size:11px;flex-shrink:0;"></i>
            <span>${q}</span>
        </button>
    `).join('');

            // Attach handlers via JS (not inline onclick) — fixes the quote bug entirely
            block.querySelectorAll('.sugg-row').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.idx, 10);
                    const q = SUGG[btn.dataset.cat][idx];
                    $('homeInput').value = q;
                    autoGrow($('homeInput'));
                    sendFromHome();
                });
            });
        }

        // ─── View switching ───────────────────────────────────────────────────────────
        function goHome() {
            $('homeView').style.display = 'flex';
            $('chatView').classList.remove('active');
            $('homeInput').value = '';
            $('homeInput').style.height = 'auto';
        }

        function goChat() {
            $('homeView').style.display = 'none';
            $('chatView').classList.add('active');
        }

        async function newChat() {
            await clearHistoryQuiet();
            $('msgInner').innerHTML = '';
            goHome();
        }

        // ─── Content renderer ─────────────────────────────────────────────────────────
        function esc(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function render(raw) {
            return raw.split(/(```[\s\S]*?```)/g).map((seg, i) => {
                if (i % 2 === 1) {
                    const code = seg.replace(/^```\w*\n?/, '').replace(/\n?```$/, '');
                    return `<pre><code>${esc(code)}</code></pre>`;
                }
                return esc(seg)
                    .replace(/`([^`\n]+?)`/g, '<code>$1</code>')
                    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n/g, '<br>');
            }).join('');
        }

        // ─── Streaming state ──────────────────────────────────────────────────────────
        let streaming = false;

        function setStreaming(on) {
            streaming = on;
            $('chatSendBtn').disabled = on;
            $('homeSendBtn').disabled = on;
            $('chatStatus').innerHTML = on ?
                `<span class="status-streaming" style="width:6px;height:6px;border-radius:50%;background:var(--c-accent);display:inline-block;"></span><span style="color:var(--c-accent);">Typing…</span>` :
                `<span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span>Online`;
        }

        // ─── Send handlers ────────────────────────────────────────────────────────────
        function homeKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendFromHome();
            }
        }

        function chatKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendFromChat();
            }
        }

        function sendFromHome() {
            const text = $('homeInput').value.trim();
            if (!text || streaming) return;
            goChat();
            setTimeout(() => doSend(text), 50);
        }

        function sendFromChat() {
            const text = $('chatInput').value.trim();
            if (!text || streaming) return;
            $('chatInput').value = '';
            $('chatInput').style.height = 'auto';
            doSend(text);
        }

        // ─── Core SSE send ────────────────────────────────────────────────────────────
        async function doSend(text) {
            addBubble(text, 'user');
            setStreaming(true);

            const dots = addTypingDots();
            let agentBubble = null;
            let agentWrap = null;
            let raw = '';
            let gotFirst = false;

            try {
                const res = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf()
                    },
                    body: JSON.stringify({
                        message: text
                    }),
                });

                const ct = res.headers.get('content-type') ?? '';

                if (!res.ok || !ct.includes('text/event-stream')) {
                    const body = await res.text();
                    rmEl(dots);
                    let msg = `Error ${res.status}`;
                    try {
                        const j = JSON.parse(body);
                        msg = j.error || j.message || msg;
                    } catch {}
                    addBubble(msg, 'agent');
                    setStreaming(false);
                    return;
                }

                const reader = res.body.getReader();
                const decoder = new TextDecoder();
                let buf = '';

                while (true) {
                    const {
                        done,
                        value
                    } = await reader.read();
                    if (done) break;
                    buf += decoder.decode(value, {
                        stream: true
                    });

                    let sep;
                    while ((sep = buf.indexOf('\n\n')) !== -1) {
                        const frame = buf.slice(0, sep);
                        buf = buf.slice(sep + 2);

                        for (const line of frame.split('\n')) {
                            if (!line.startsWith('data:')) continue;
                            const payload = line.slice(5).trimStart();
                            if (!payload) continue;

                            let msg;
                            try {
                                msg = JSON.parse(payload);
                            } catch {
                                continue;
                            }

                            if (msg.error) throw new Error(msg.error);

                            if (typeof msg.chunk === 'string' && msg.chunk !== '') {
                                if (!gotFirst) {
                                    rmEl(dots);
                                    const r = addStreamBubble();
                                    agentBubble = r.bubble;
                                    agentWrap = r.wrap;
                                    gotFirst = true;
                                }
                                raw += msg.chunk;
                                agentBubble.innerHTML = render(raw);
                                agentBubble.classList.add('cursor-blink');
                                scrollEnd();
                            }

                            if (msg.done === true) {
                                if (agentBubble) {
                                    agentBubble.classList.remove('cursor-blink');
                                    agentBubble.innerHTML = render(raw);
                                    stamp(agentWrap);
                                }
                            }
                        }
                    }
                }

                if (agentBubble) agentBubble.classList.remove('cursor-blink');
                if (!gotFirst) rmEl(dots);

            } catch (err) {
                rmEl(dots);
                if (agentBubble) agentBubble.classList.remove('cursor-blink');
                else addBubble('Something went wrong. Please try again.', 'agent');
                console.error('[SSE]', err);
                Swal.fire({
                    icon: 'error',
                    text: err.message || 'Connection error.',
                    timer: 3000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            } finally {
                setStreaming(false);
                $('chatInput').focus();
            }
        }

        // ─── Clear history ────────────────────────────────────────────────────────────
        async function clearHistory() {
            if (streaming) return;
            const {
                isConfirmed
            } = await Swal.fire({
                title: 'Clear conversation?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Clear',
                confirmButtonColor: '#ef4444',
            });
            if (!isConfirmed) return;
            await clearHistoryQuiet();
            $('msgInner').innerHTML = '';
            addSysMsg('Conversation cleared');
        }

        // FIX: always send a JSON body so Doppar middleware doesn't reject with 500
        async function clearHistoryQuiet() {
            try {
                await fetch('/ai/clear-history', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({}), // ← was missing — caused 500
                });
            } catch {}
        }

        // ─── Load history ─────────────────────────────────────────────────────────────
        async function boot() {
            renderSugg('framework');
            try {
                const r = await fetch('/ai/history');
                const d = await r.json();
                if (d.success && d.messages?.length) {
                    const msgs = d.messages.filter(m => m.role !== 'system');
                    if (msgs.length) {
                        goChat();
                        msgs.forEach(m => addBubble(m.content, m.role === 'user' ? 'user' : 'agent'));
                        addSysMsg('Previous conversation restored');
                        return;
                    }
                }
            } catch {}
        }

        // ─── UI primitives ────────────────────────────────────────────────────────────
        function msgInner() {
            return $('msgInner');
        }

        function addBubble(content, role) {
            const inner = msgInner();
            const row = document.createElement('div');
            row.className = 'anim-fade-in';

            if (role === 'user') {
                row.style.cssText = 'display:flex;justify-content:flex-end;gap:8px;align-items:flex-end;';
                row.innerHTML = `
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;max-width:72%;">
                <div class="msg-bubble bubble-user user-bubble">${render(content)}</div>
                <span style="font-size:.68rem;color:var(--c-muted);padding:0 2px;">${now()}</span>
            </div>
            <div style="width:26px;height:26px;border-radius:7px;background:var(--c-ink);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;flex-shrink:0;">ME</div>
        `;
            } else {
                row.style.cssText = 'display:flex;gap:10px;align-items:flex-end;';
                row.innerHTML = `
            <div style="width:26px;height:26px;border-radius:7px;background:var(--c-accent-l,#e8f0fe);display:flex;align-items:center;justify-content:center;color:var(--c-accent);font-size:10px;font-weight:700;flex-shrink:0;background:#e8f0fe;">D</div>
            <div style="display:flex;flex-direction:column;gap:4px;max-width:78%;">
                <div class="msg-bubble bubble-agent">${render(content)}</div>
                <span style="font-size:.68rem;color:var(--c-muted);padding:0 2px;">${now()}</span>
            </div>
        `;
            }
            inner.appendChild(row);
            scrollEnd();
            return row;
        }

        function addStreamBubble() {
            const inner = msgInner();
            const row = document.createElement('div');
            row.className = 'anim-fade-in';
            row.style.cssText = 'display:flex;gap:10px;align-items:flex-end;';

            const av = document.createElement('div');
            av.style.cssText = 'width:26px;height:26px;border-radius:7px;background:#e8f0fe;display:flex;align-items:center;justify-content:center;color:#1a6ef5;font-size:10px;font-weight:700;flex-shrink:0;';
            av.textContent = 'D';

            const col = document.createElement('div');
            col.style.cssText = 'display:flex;flex-direction:column;gap:4px;max-width:78%;';

            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble bubble-agent';

            col.appendChild(bubble);
            row.appendChild(av);
            row.appendChild(col);
            inner.appendChild(row);
            scrollEnd();
            return {
                bubble,
                wrap: col
            };
        }

        function addTypingDots() {
            const inner = msgInner();
            const row = document.createElement('div');
            row.className = 'anim-fade-in';
            row.style.cssText = 'display:flex;gap:10px;align-items:flex-end;';
            row.innerHTML = `
        <div style="width:26px;height:26px;border-radius:7px;background:#e8f0fe;display:flex;align-items:center;justify-content:center;color:#1a6ef5;font-size:10px;font-weight:700;flex-shrink:0;">D</div>
        <div style="background:var(--c-card);border:1px solid var(--c-border);border-radius:16px;border-bottom-left-radius:3px;padding:12px 14px;display:flex;gap:5px;align-items:center;">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
        </div>
    `;
            inner.appendChild(row);
            scrollEnd();
            return row;
        }

        function stamp(wrap) {
            const t = document.createElement('span');
            t.style.cssText = 'font-size:.68rem;color:var(--c-muted);padding:0 2px;';
            t.textContent = now();
            wrap.appendChild(t);
            scrollEnd();
        }

        function addSysMsg(text) {
            const inner = msgInner();
            const d = document.createElement('div');
            d.style.cssText = 'display:flex;justify-content:center;';
            d.innerHTML = `<span style="font-size:.72rem;color:var(--c-muted);background:var(--c-bg);border:1px solid var(--c-border);border-radius:9999px;padding:3px 12px;">${text}</span>`;
            inner.appendChild(d);
            scrollEnd();
        }

        function scrollEnd() {
            requestAnimationFrame(() => {
                const m = $('chatMessages');
                if (m) m.scrollTop = m.scrollHeight;
            });
        }

        document.addEventListener('DOMContentLoaded', boot);
    </script>
</body>

</html>