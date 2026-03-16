<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Doppar AI — Framework Assistant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="[[ csrf_token() ]]">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --ink: #0d0f14;
            --paper: #f5f3ef;
            --surface: #ffffff;
            --muted: #8a8880;
            --border: #e2e0da;
            --accent: #2a52be;
            --accent-lt: #dce6ff;
            --accent-dk: #1a3a9c;
            --code-bg: #1c1f2a;
            --code-fg: #c9d1e8;
            --green: #1f7a52;
            --red: #b53030;
            --radius: 12px;
            --radius-sm: 6px;
            --shadow: 0 2px 12px rgba(13, 15, 20, .08);
            --shadow-lg: 0 8px 40px rgba(13, 15, 20, .14);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--paper);
            font-family: 'Geist', sans-serif;
            color: var(--ink);
            min-height: 100vh;
        }

        /* NAV */
        .nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: .95rem;
            color: var(--ink);
            text-decoration: none;
        }

        .nav-brand-dot {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 24px;
        }

        .nav-links a {
            color: var(--muted);
            font-size: .88rem;
            text-decoration: none;
            transition: color .2s;
        }

        .nav-links a:hover {
            color: var(--ink);
        }

        .hero {
            padding: 72px 32px 56px;
            text-align: center;
            max-width: 720px;
            margin: 0 auto;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent-lt);
            color: var(--accent);
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-family: 'Instrument Serif', serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 400;
            line-height: 1.18;
            letter-spacing: -.02em;
            margin-bottom: 16px;
        }

        .hero h1 em {
            font-style: italic;
            color: var(--accent);
        }

        .hero p {
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.65;
            max-width: 520px;
            margin: 0 auto 32px;
        }

        .feature-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-bottom: 48px;
        }

        .pill {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: .8rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pill i {
            color: var(--accent);
            font-size: .85rem;
        }

        /* CARDS */
        .cards {
            max-width: 960px;
            margin: 0 auto 80px;
            padding: 0 32px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            transition: transform .2s, box-shadow .2s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--accent-lt);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 16px;
        }

        .card h3 {
            font-size: .95rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .card p {
            font-size: .83rem;
            color: var(--muted);
            line-height: 1.55;
        }

        /* CHAT WIDGET */
        .chat-widget {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1050;
        }

        .chat-fab {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            border: none;
            font-size: 22px;
            box-shadow: 0 4px 20px rgba(42, 82, 190, .35);
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .chat-fab:hover {
            transform: scale(1.06);
            box-shadow: 0 6px 28px rgba(42, 82, 190, .45);
        }

        .fab-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #e84040;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .chat-panel {
            position: absolute;
            bottom: 70px;
            right: 0;
            width: 420px;
            height: 580px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-panel.open {
            display: flex;
            animation: panelIn .22s ease;
        }

        @keyframes panelIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .panel-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
            flex-shrink: 0;
        }

        .panel-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-avatar {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
        }

        .panel-title {
            font-weight: 600;
            font-size: .9rem;
        }

        .panel-subtitle {
            font-size: .75rem;
            color: var(--green);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .panel-subtitle::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
        }

        .panel-subtitle.streaming {
            color: var(--accent);
        }

        .panel-subtitle.streaming::before {
            background: var(--accent);
            animation: pulse .8s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .3
            }
        }

        .panel-actions {
            display: flex;
            gap: 4px;
        }

        .panel-icon-btn {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            background: transparent;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, color .15s;
        }

        .panel-icon-btn:hover {
            background: var(--paper);
            color: var(--ink);
        }

        .panel-icon-btn.danger:hover {
            background: #fde8e8;
            color: var(--red);
        }

        /* MESSAGES */
        .panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 18px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .panel-body::-webkit-scrollbar {
            width: 4px;
        }

        .panel-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .panel-body::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 2px;
        }

        .sys-msg {
            text-align: center;
            font-size: .78rem;
            color: var(--muted);
            padding: 6px 12px;
            background: var(--paper);
            border-radius: 20px;
            align-self: center;
        }

        .msg-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .msg-row.user {
            flex-direction: row-reverse;
        }

        .msg-avatar {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .msg-row.agent .msg-avatar {
            background: var(--accent-lt);
            color: var(--accent);
        }

        .msg-row.user .msg-avatar {
            background: var(--ink);
            color: #fff;
        }

        .msg-wrap {
            display: flex;
            flex-direction: column;
            max-width: 80%;
        }

        .msg-row.user .msg-wrap {
            align-items: flex-end;
        }

        .msg-bubble {
            padding: 10px 14px;
            border-radius: var(--radius);
            font-size: .87rem;
            line-height: 1.6;
            word-break: break-word;
            animation: bubbleIn .18s ease;
        }

        @keyframes bubbleIn {
            from {
                opacity: 0;
                transform: translateY(6px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .msg-row.agent .msg-bubble {
            background: var(--paper);
            border: 1px solid var(--border);
            border-bottom-left-radius: 3px;
        }

        .msg-row.user .msg-bubble {
            background: var(--accent);
            color: #fff;
            border-bottom-right-radius: 3px;
        }

        .msg-time {
            font-size: .69rem;
            color: var(--muted);
            margin-top: 4px;
            padding: 0 2px;
        }

        .msg-bubble pre {
            background: var(--code-bg);
            color: var(--code-fg);
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            overflow-x: auto;
            font-family: 'DM Mono', monospace;
            font-size: .79rem;
            line-height: 1.55;
            margin: 8px 0;
            white-space: pre;
        }

        .msg-bubble code {
            font-family: 'DM Mono', monospace;
            font-size: .81rem;
            background: rgba(42, 82, 190, .1);
            color: var(--accent);
            padding: 1px 5px;
            border-radius: 3px;
        }

        .msg-row.user .msg-bubble code {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .msg-bubble strong {
            font-weight: 600;
        }

        /* Streaming cursor */
        .cursor-blink::after {
            content: '▋';
            animation: blink .55s step-end infinite;
            color: var(--accent);
            font-size: .9em;
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

        .typing-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .typing-dots {
            display: flex;
            gap: 5px;
            padding: 12px 14px;
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            border-bottom-left-radius: 3px;
        }

        .typing-dots span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--muted);
            animation: dot 1.1s infinite;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: .18s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: .36s;
        }

        @keyframes dot {

            0%,
            60%,
            100% {
                transform: translateY(0);
                opacity: .45
            }

            30% {
                transform: translateY(-5px);
                opacity: 1
            }
        }

        .panel-footer {
            padding: 10px 14px 13px;
            border-top: 1px solid var(--border);
            background: var(--surface);
            flex-shrink: 0;
        }

        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 8px;
        }

        .suggestion-chip {
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .74rem;
            color: var(--muted);
            cursor: pointer;
            transition: border-color .15s, color .15s, background .15s;
            white-space: nowrap;
        }

        .suggestion-chip:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: var(--accent-lt);
        }

        .input-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .chat-textarea {
            flex: 1;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 9px 12px;
            font-family: 'Geist', sans-serif;
            font-size: .87rem;
            color: var(--ink);
            resize: none;
            max-height: 100px;
            min-height: 38px;
            transition: border-color .2s, box-shadow .2s;
            background: var(--paper);
            line-height: 1.4;
            overflow-y: auto;
        }

        .chat-textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(42, 82, 190, .12);
            background: var(--surface);
        }

        .send-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--accent);
            border: none;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .15s, transform .1s;
        }

        .send-btn:hover:not(:disabled) {
            background: var(--accent-dk);
            transform: scale(1.04);
        }

        .send-btn:disabled {
            background: var(--border);
            color: var(--muted);
            cursor: not-allowed;
        }

        .footer-hint {
            font-size: .71rem;
            color: var(--muted);
            margin-top: 6px;
            text-align: center;
        }
    </style>
</head>

<body>

    <nav class="nav">
        <a class="nav-brand" href="#">
            <div class="nav-brand-dot">D</div>
            Doppar Framework
        </a>
        <div class="nav-links">
            <a href="#">Docs</a>
            <a href="#">Packages</a>
            <a href="#">Community</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-label"><i class="bi bi-cpu"></i> AI-Powered Docs Assistant</div>
        <h1>Ask anything about<br><em>Doppar Framework</em></h1>
        <p>Get instant answers on Pipeline tasks, LLM Agents, streaming, persistence, RAG workflows, and more — powered by your own Doppar AI component.</p>
        <div class="feature-pills">
            <div class="pill"><i class="bi bi-broadcast"></i> Live Streaming</div>
            <div class="pill"><i class="bi bi-database"></i> Conversation Persistence</div>
            <div class="pill"><i class="bi bi-lightning-charge"></i> Multi-turn Context</div>
            <div class="pill"><i class="bi bi-shield-check"></i> Rate Limited</div>
            <div class="pill"><i class="bi bi-code-slash"></i> Code Examples</div>
        </div>
    </section>

    <div class="cards">
        <div class="card">
            <div class="card-icon"><i class="bi bi-layers"></i></div>
            <h3>Pipeline Tasks</h3>
            <p>Run 15+ ML tasks locally — sentiment analysis, translation, QA, NER, image classification, ASR, and more.</p>
        </div>
        <div class="card">
            <div class="card-icon"><i class="bi bi-robot"></i></div>
            <h3>LLM Agents</h3>
            <p>Fluent API for OpenAI, Gemini, Claude, OpenRouter, and self-hosted models with full parameter control.</p>
        </div>
        <div class="card">
            <div class="card-icon"><i class="bi bi-arrow-repeat"></i></div>
            <h3>Streaming & Persistence</h3>
            <p>Token-by-token streaming via SSE and stateful multi-turn conversations with pluggable store backends.</p>
        </div>
    </div>

    <!-- Chat Widget -->
    <div class="chat-widget">
        <button class="chat-fab" id="chatToggle" aria-label="Toggle AI chat">
            <i class="bi bi-chat-dots-fill" id="fabIcon"></i>
            <div class="fab-badge" id="fabBadge">!</div>
        </button>

        <div class="chat-panel" id="chatPanel">
            <div class="panel-header">
                <div class="panel-header-left">
                    <div class="panel-avatar">D</div>
                    <div>
                        <div class="panel-title">Doppar Assistant</div>
                        <div class="panel-subtitle" id="panelStatus">Online</div>
                    </div>
                </div>
                <div class="panel-actions">
                    <button class="panel-icon-btn danger" id="clearBtn" title="Clear conversation">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <button class="panel-icon-btn" id="chatClose" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="panel-body" id="chatMessages"></div>

            <div class="panel-footer">
                <div class="suggestions" id="suggestions">
                    <span class="suggestion-chip" data-q="How do I install Doppar AI?">Install Doppar AI</span>
                    <span class="suggestion-chip" data-q="Show me a streaming example with OpenAI">Streaming example</span>
                    <span class="suggestion-chip" data-q="How does Agent Persistence work?">Persistence</span>
                    <span class="suggestion-chip" data-q="What Pipeline tasks are available?">Pipeline tasks</span>
                </div>
                <div class="input-row">
                    <textarea class="chat-textarea" id="msgInput" rows="1"
                        placeholder="Ask about Doppar…" autocomplete="off"></textarea>
                    <button class="send-btn" id="sendBtn"><i class="bi bi-send-fill"></i></button>
                </div>
                <div class="footer-hint">Powered by Doppar AI · OpenAI GPT</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        'use strict';

        const el = id => document.getElementById(id);
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const timeNow = () => new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        const toast = (icon, text) =>
            Swal.fire({
                icon,
                text,
                timer: 3500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        /**
         * Convert raw AI text (with markdown) → safe HTML.
         * Splits on fenced code blocks first so we never mangle code content.
         */
        function renderContent(raw) {
            const segments = raw.split(/(```[\s\S]*?```)/g);
            return segments.map((seg, i) => {
                if (i % 2 === 1) {
                    // Fenced code block
                    const code = seg.replace(/^```\w*\n?/, '').replace(/\n?```$/, '');
                    return `<pre><code>${escapeHtml(code)}</code></pre>`;
                }

                return escapeHtml(seg)
                    .replace(/`([^`\n]+?)`/g, '<code>$1</code>')
                    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n/g, '<br>');
            }).join('');
        }

        const chatPanel = el('chatPanel');
        const chatToggle = el('chatToggle');
        const chatClose = el('chatClose');
        const clearBtn = el('clearBtn');
        const chatMsgs = el('chatMessages');
        const msgInput = el('msgInput');
        const sendBtn = el('sendBtn');
        const fabIcon = el('fabIcon');
        const fabBadge = el('fabBadge');
        const panelStatus = el('panelStatus');
        const suggestions = el('suggestions');

        let isStreaming = false;
        let panelOpen = false;

        chatToggle.addEventListener('click', () => {
            panelOpen = !panelOpen;
            chatPanel.classList.toggle('open', panelOpen);
            fabIcon.className = panelOpen ? 'bi bi-x-lg' : 'bi bi-chat-dots-fill';
            if (panelOpen) {
                fabBadge.style.display = 'none';
                msgInput.focus();
                scrollBottom();
            }
        });
        chatClose.addEventListener('click', () => chatToggle.click());

        msgInput.addEventListener('input', () => {
            msgInput.style.height = 'auto';
            msgInput.style.height = Math.min(msgInput.scrollHeight, 100) + 'px';
        });
        msgInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        sendBtn.addEventListener('click', sendMessage);

        suggestions.addEventListener('click', e => {
            const chip = e.target.closest('.suggestion-chip');
            if (!chip || isStreaming) return;
            msgInput.value = chip.dataset.q;
            sendMessage();
        });

        clearBtn.addEventListener('click', async () => {
            if (isStreaming) return;
            const {
                isConfirmed
            } = await Swal.fire({
                title: 'Clear conversation?',
                text: 'All history will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Clear',
                confirmButtonColor: '#b53030',
            });
            if (!isConfirmed) return;
            try {
                const r = await fetch('/ai/clear-history', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'Content-Type': 'application/json'
                    },
                });
                const d = await r.json();
                if (d.success) {
                    chatMsgs.innerHTML = '';
                    addSysMsg('Conversation cleared — starting fresh');
                    addBubble("Hi! I'm your Doppar assistant. What would you like to know?", 'agent');
                }
            } catch {
                toast('error', 'Could not clear history.');
            }
        });

        async function loadHistory() {
            try {
                const r = await fetch('/ai/history');
                const d = await r.json();
                if (d.success && Array.isArray(d.messages) && d.messages.length > 0) {
                    d.messages
                        .filter(m => m.role !== 'system')
                        .forEach(m => addBubble(m.content, m.role === 'user' ? 'user' : 'agent'));
                    addSysMsg('Previous conversation restored');
                    return;
                }
            } catch {

            }
            addBubble(
                "Hi! I'm your **Doppar Framework** assistant.\n\nAsk me about Pipeline tasks, LLM Agents, streaming, persistence, RAG, or any Doppar feature — I'll give you working code examples.\n\nWhat would you like to explore?",
                'agent'
            );
        }

        async function sendMessage() {
            const text = msgInput.value.trim();
            if (!text || isStreaming) return;

            suggestions.style.display = 'none';
            addBubble(text, 'user');
            msgInput.value = '';
            msgInput.style.height = 'auto';
            setStreaming(true);

            const typingEl = addTypingDots();
            let agentBubble = null;
            let agentWrap = null;
            let rawContent = '';
            let firstChunk = true;

            try {
                const response = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: JSON.stringify({
                        message: text
                    }),
                });

                if (!response.ok) {
                    let msg = `Server error ${response.status}`;
                    try {
                        const d = await response.json();
                        msg = d.error || msg;
                    } catch {}
                    throw new Error(msg);
                }

                const contentType = response.headers.get('Content-Type') || '';

                // Non-streaming fallback
                if (!contentType.includes('text/event-stream')) {
                    const d = await response.json();
                    removeEl(typingEl);
                    addBubble(d.response || d.error || 'No response received.', 'agent');
                    setStreaming(false);
                    return;
                }

                // SSE stream reader 
                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');
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

                    // SSE frames delimited by double newline
                    let boundary;
                    while ((boundary = buf.indexOf('\n\n')) !== -1) {
                        const frame = buf.slice(0, boundary);
                        buf = buf.slice(boundary + 2);

                        for (const line of frame.split('\n')) {
                            if (!line.startsWith('data: ')) continue;
                            const jsonStr = line.slice(6).trim();
                            if (!jsonStr) continue;

                            let payload;
                            try {
                                payload = JSON.parse(jsonStr);
                            } catch {
                                continue;
                            } // malformed frame — skip

                            if (payload.error) throw new Error(payload.error);

                            if (typeof payload.chunk === 'string' && payload.chunk !== '') {
                                // First chunk → swap typing dots for real bubble
                                if (firstChunk) {
                                    removeEl(typingEl);
                                    const r = addStreamBubble();
                                    agentBubble = r.bubble;
                                    agentWrap = r.wrap;
                                    firstChunk = false;
                                }
                                rawContent += payload.chunk;
                                agentBubble.innerHTML = renderContent(rawContent);
                                agentBubble.classList.add('cursor-blink');
                                scrollBottom();
                            }

                            if (payload.done === true) {
                                if (agentBubble) {
                                    agentBubble.classList.remove('cursor-blink');
                                    agentBubble.innerHTML = renderContent(rawContent);
                                    const t = document.createElement('div');
                                    t.className = 'msg-time';
                                    t.textContent = timeNow();
                                    agentWrap.appendChild(t);
                                    scrollBottom();
                                }
                                if (!panelOpen) fabBadge.style.display = 'flex';
                            }
                        }
                    }
                }

                // Ensure cursor is removed if stream ends without {done:true}
                if (agentBubble) agentBubble.classList.remove('cursor-blink');
                if (firstChunk) removeEl(typingEl); // no chunks at all

            } catch (err) {
                removeEl(typingEl);
                if (agentBubble) {
                    agentBubble.classList.remove('cursor-blink');
                } else {
                    addBubble('Sorry, something went wrong. Please try again.', 'agent');
                }
                console.error('[SSE]', err);
                toast('error', err.message || 'Connection error.');
            } finally {
                setStreaming(false);
                msgInput.focus();
            }
        }

        function setStreaming(active) {
            isStreaming = active;
            sendBtn.disabled = active;
            panelStatus.textContent = active ? 'Typing…' : 'Online';
            panelStatus.className = active ? 'panel-subtitle streaming' : 'panel-subtitle';
        }

        function addBubble(content, role) {
            const row = document.createElement('div');
            row.className = `msg-row ${role}`;

            const avatar = document.createElement('div');
            avatar.className = 'msg-avatar';
            avatar.textContent = role === 'user' ? 'ME' : 'D';

            const wrap = document.createElement('div');
            wrap.className = 'msg-wrap';

            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble';
            bubble.innerHTML = renderContent(content);

            const time = document.createElement('div');
            time.className = 'msg-time';
            time.textContent = timeNow();

            wrap.appendChild(bubble);
            wrap.appendChild(time);
            row.appendChild(avatar);
            row.appendChild(wrap);
            chatMsgs.appendChild(row);
            scrollBottom();
            return row;
        }

        /** Empty agent bubble for streaming into. */
        function addStreamBubble() {
            const row = document.createElement('div');
            row.className = 'msg-row agent';

            const avatar = document.createElement('div');
            avatar.className = 'msg-avatar';
            avatar.textContent = 'D';

            const wrap = document.createElement('div');
            wrap.className = 'msg-wrap';

            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble';

            wrap.appendChild(bubble);
            row.appendChild(avatar);
            row.appendChild(wrap);
            chatMsgs.appendChild(row);
            scrollBottom();
            return {
                bubble,
                wrap
            };
        }

        function addTypingDots() {
            const row = document.createElement('div');
            row.className = 'typing-row';

            const av = document.createElement('div');
            av.className = 'msg-avatar';
            av.style.cssText = 'background:var(--accent-lt);color:var(--accent);width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;';
            av.textContent = 'D';

            const dots = document.createElement('div');
            dots.className = 'typing-dots';
            dots.innerHTML = '<span></span><span></span><span></span>';

            row.appendChild(av);
            row.appendChild(dots);
            chatMsgs.appendChild(row);
            scrollBottom();
            return row;
        }

        function removeEl(node) {
            if (node && node.parentNode) node.parentNode.removeChild(node);
        }

        function addSysMsg(text) {
            const d = document.createElement('div');
            d.className = 'sys-msg';
            d.textContent = text;
            chatMsgs.appendChild(d);
            scrollBottom();
        }

        function scrollBottom() {
            requestAnimationFrame(() => {
                chatMsgs.scrollTop = chatMsgs.scrollHeight;
            });
        }

        document.addEventListener('DOMContentLoaded', loadHistory);
    </script>
</body>

</html>