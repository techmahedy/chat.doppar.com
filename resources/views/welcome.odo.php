<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Doppar AI</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="[[ csrf_token() ]]">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/lib/highlight.min.js"></script>
    <link rel="stylesheet" href="[[ enqueue('style.css') ]]">
</head>

<body>
    <div id="app">
        <aside id="sidebar">

            <div class="sb-head">
                <span class="sb-wordmark">doppar <em>ai</em></span>
                <button class="sb-toggle" id="sbToggle" onclick="toggleSB()" title="Toggle sidebar">
                    <i class="bi bi-layout-sidebar-reverse" id="sbToggleIcon"></i>
                </button>
            </div>

            <button class="sb-new" onclick="newChat()">
                <i class="bi bi-plus sb-new-icon"></i>
                <span class="sb-new-txt">New conversation</span>
            </button>

            <div class="sb-label">Recent</div>
            <div class="sb-list" id="sbList"></div>

            <div class="sb-foot">
                <!-- Status indicator -->
                <div class="sb-status">
                    <span class="s-dot" id="sDot"></span>
                    <span class="sb-foot-lbl" id="sTxt">Online</span>
                </div>

                <button class="sb-foot-btn">
                    <i class="bi bi-gear"></i>
                    <span class="sb-foot-lbl">Settings</span>
                </button>
                <button class="sb-foot-btn danger" onclick="clearAllHistory()">
                    <i class="bi bi-trash3"></i>
                    <span class="sb-foot-lbl">Clear all history</span>
                </button>
            </div>

        </aside>

        <div id="main">

            <div id="homeView">
                <div class="home-inner">

                    <div class="home-brand">
                        <p>Framework assistant · powered by doppar/ai · OpenAI GPT</p>
                    </div>

                    <div class="search-box">
                        <textarea id="homeInput" rows="1" placeholder="Ask anything about Doppar Framework…"
                            oninput="autoGrow(this)" onkeydown="homeKD(event)"></textarea>
                        <div class="search-foot">
                            <div class="model-chip"><i class="bi bi-cpu"></i><span id="modelLbl">gpt-4o-mini</span></div>
                            <button class="send-btn" id="homeSendBtn" onclick="sendHome()">
                                <i class="bi bi-arrow-up"></i>
                            </button>
                        </div>
                    </div>

                    <div class="cat-row">
                        <button class="cat-btn active" data-cat="framework" onclick="selCat(this,'framework')"><i class="bi bi-layers"></i>Framework</button>
                        <button class="cat-btn" data-cat="pipeline" onclick="selCat(this,'pipeline')"><i class="bi bi-diagram-3"></i>Pipeline</button>
                        <button class="cat-btn" data-cat="agents" onclick="selCat(this,'agents')"><i class="bi bi-robot"></i>Agents</button>
                        <button class="cat-btn" data-cat="streaming" onclick="selCat(this,'streaming')"><i class="bi bi-broadcast"></i>Streaming</button>
                        <button class="cat-btn" data-cat="persistence" onclick="selCat(this,'persistence')"><i class="bi bi-database"></i>Persistence</button>
                        <button class="cat-btn" data-cat="responses" onclick="selCat(this,'responses')"><i class="bi bi-send"></i>Responses</button>
                    </div>

                    <div class="sugg-block" id="suggBlock"></div>

                </div>
            </div>

            <div id="chatView">

                <div id="chatMessages">
                    <div id="msgInner"></div>
                </div>

                <div id="chatInputArea">
                    <div class="chat-input-wrap">
                        <div class="chat-box">
                            <textarea id="chatInput" rows="1" placeholder="Ask a follow-up…"
                                oninput="autoGrow(this)" onkeydown="chatKD(event)"></textarea>
                            <div class="chat-box-foot">
                                <div class="model-chip"><i class="bi bi-cpu"></i>gpt-4o-mini</div>
                                <button class="send-btn" id="chatSendBtn" onclick="sendChat()">
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                            </div>
                        </div>
                        <p class="hint">Powered by Doppar AI · doppar/ai package · Press Enter to send, Shift+Enter for newline</p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        'use strict';

        const $ = id => document.getElementById(id);
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const now = () => new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
        const rmEl = n => n?.parentNode?.removeChild(n);
        const toast = (icon, text) => Swal.fire({
            icon,
            text,
            timer: 3200,
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        });

        function autoGrow(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 130) + 'px';
        }

        function toggleSB() {
            const isNowCollapsed = $('sidebar').classList.toggle('collapsed');
            $('sbToggleIcon').className = isNowCollapsed ?
                'bi bi-layout-sidebar' :
                'bi bi-layout-sidebar-reverse';
        }

        let chatHistory = [];
        let activeChatId = null;

        function loadHistory() {
            try {
                chatHistory = JSON.parse(localStorage.getItem('doppar_chats') || '[]');
            } catch {
                chatHistory = [];
            }
        }

        function saveHistory() {
            localStorage.setItem('doppar_chats', JSON.stringify(chatHistory));
        }

        function addToHistory(id, title) {
            chatHistory = chatHistory.filter(c => c.id !== id);
            chatHistory.unshift({
                id,
                title,
                ts: Date.now()
            });
            if (chatHistory.length > 40) chatHistory = chatHistory.slice(0, 40);
            saveHistory();
            renderSB();
        }

        function makeTitle(msg) {
            const words = msg.trim().split(/\s+/).slice(0, 7).join(' ');
            return words.length < msg.trim().length ? words + '…' : words;
        }

        function renderSB() {
            const list = $('sbList');
            if (!chatHistory.length) {
                list.innerHTML = `<div class="sb-empty"><i class="bi bi-chat-square-dots"></i>No chat yet</div>`;
                return;
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);

            let html = '',
                lastGroup = '';
            chatHistory.forEach(c => {
                const d = new Date(c.ts);
                d.setHours(0, 0, 0, 0);
                let group = d >= today ? 'Today' : d >= yesterday ? 'Yesterday' : 'Older';
                if (group !== lastGroup) {
                    html += `<div class="sb-date-group">${group}</div>`;
                    lastGroup = group;
                }
                const active = c.id === activeChatId ? 'active' : '';
                html += `
      <div class="sb-item ${active}" data-id="${c.id}">
        <span class="sb-item-ico"><i class="bi bi-chat-text"></i></span>
        <span class="sb-item-lbl" title="${esc(c.title)}">${esc(c.title)}</span>
        <button class="sb-item-del" title="Delete" data-del="${c.id}">
          <i class="bi bi-x"></i>
        </button>
      </div>`;
            });
            list.innerHTML = html;

            list.querySelectorAll('.sb-item').forEach(el => {
                el.addEventListener('click', e => {
                    if (e.target.closest('.sb-item-del')) return;
                    loadChat(el.dataset.id);
                });
            });
            list.querySelectorAll('.sb-item-del').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.stopPropagation();
                    deleteChat(btn.dataset.del);
                });
            });
        }

        async function loadChat(id) {
            if (streaming) return;
            activeChatId = id;
            const chat = chatHistory.find(c => c.id === id);
            if (!chat) return;
            renderSB();
            setTopTitle(chat.title);
            $('msgInner').innerHTML = '';
            _codeBlockId = 0; // Reset code block ID counter to prevent conflicts

            try {
                const r = await fetch(`/ai/history?chat_id=${encodeURIComponent(id)}`);
                const d = await r.json();
                if (d.success && d.messages?.length) {
                    d.messages.filter(m => m.role !== 'system')
                        .forEach(m => addBubble(m.content, m.role === 'user' ? 'user' : 'agent'));
                    addSysMsg('Conversation restored');
                } else {
                    addSysMsg('No previous messages found');
                }
            } catch (error) {
                console.error('Error loading chat:', error);
                addSysMsg('Error loading conversation');
            }

            // Show chat view after loading messages
            goChat();
        }

        async function deleteChat(id) {
            chatHistory = chatHistory.filter(c => c.id !== id);
            saveHistory();
            if (activeChatId === id) {
                await clearHistoryQuiet(id);
                activeChatId = null;
                $('msgInner').innerHTML = '';
                _codeBlockId = 0; // Reset code block ID counter to prevent conflicts
                goHome();
                setTopTitle(null);
            }
            renderSB();
        }

        async function clearAllHistory() {
            if (streaming) return;
            const {
                isConfirmed
            } = await Swal.fire({
                title: 'Clear all history?',
                text: 'All conversations will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Clear all',
                confirmButtonColor: '#dc2626'
            });
            if (!isConfirmed) return;
            chatHistory = [];
            saveHistory();
            await clearHistoryQuiet();
            activeChatId = null;
            $('msgInner').innerHTML = '';
            _codeBlockId = 0; // Reset code block ID counter to prevent conflicts
            goHome();
            setTopTitle(null);
            renderSB();
            toast('success', 'All history cleared');
        }

        function setTopTitle(_title) {

        }

        const SUGG = {
            framework: [
                'What is Doppar Framework and who created it?',
                'How does attribute-based routing work in Doppar?',
                'How do I register and use Middleware in Doppar?',
                'What is the Repository pattern and how do I use it?',
                'How do I install and configure Doppar from scratch?',
            ],
            pipeline: [
                'What Pipeline tasks are available in doppar/ai?',
                'How do I run sentiment analysis with Pipeline?',
                'Show me a translation example using Pipeline',
                'How do I do zero-shot image classification locally?',
                'How does Automatic Speech Recognition (ASR) work?',
            ],
            agents: [
                'How do I create an OpenAI Agent in Doppar?',
                'How do I use Google Gemini as an LLM agent?',
                'How do I connect Claude Anthropic with doppar/ai?',
                'How do I run a self-hosted model with SelfHost agent?',
                'How do I set a system prompt for an Agent?',
            ],
            streaming: [
                'How does token streaming work with doppar/ai?',
                'What is the difference between stream() and withStreaming()?',
                'Show me a full PHP SSE streaming controller example',
                'How do I stream with a system message and custom parameters?',
                'How do I consume a PHP SSE stream in the browser with fetch?',
            ],
            persistence: [
                'What is Agent Persistence in doppar/ai?',
                'How do I save and restore a conversation with CacheStore?',
                'How do I build a stateful multi-turn conversation in PHP?',
                'How do I implement a custom database-backed store?',
                'How do I scope chat history per user with auth()->id()?',
            ],
            responses: [
                'How do I return a JSON response in Doppar?',
                'How does response()->stream() work step by step?',
                'How do I trigger a file download with response()->download()?',
                'How do I redirect to a named route with parameters?',
                'How do I set Cache-Control and caching headers in Doppar?',
            ],
        };

        function selCat(btn, cat) {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderSugg(cat);
        }

        function renderSugg(cat) {
            const block = $('suggBlock');
            const items = SUGG[cat] || [];
            block.innerHTML = items.map((q, i) => `
    <button class="sugg-row" data-i="${i}" data-cat="${cat}">
      <i class="bi bi-arrow-up-right sugg-arrow"></i>
      <span>${esc(q)}</span>
    </button>
  `).join('');
            block.querySelectorAll('.sugg-row').forEach(btn => {
                btn.addEventListener('click', () => {
                    const q = SUGG[btn.dataset.cat][+btn.dataset.i];
                    $('homeInput').value = q;
                    autoGrow($('homeInput'));
                    sendHome();
                });
            });
        }

        function goHome() {
            $('homeView').classList.remove('gone');
            $('chatView').classList.remove('active');
            $('homeInput').value = '';
            $('homeInput').style.height = 'auto';
        }

        function goChat() {
            $('homeView').classList.add('gone');
            $('chatView').classList.add('active');
        }

        async function newChat() {
            if (streaming) return;
            activeChatId = null;
            $('msgInner').innerHTML = '';
            _codeBlockId = 0; // Reset code block ID counter to prevent conflicts
            setTopTitle(null);
            renderSB();
            goHome();
        }

        function esc(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        const LANG_MAP = {
            php: 'php',
            js: 'javascript',
            javascript: 'javascript',
            ts: 'typescript',
            typescript: 'typescript',
            html: 'xml',
            css: 'css',
            json: 'json',
            sql: 'sql',
            bash: 'bash',
            sh: 'bash',
            shell: 'bash',
            python: 'python',
            py: 'python',
            ruby: 'ruby',
            java: 'java',
            go: 'go',
            rust: 'rust',
            cpp: 'cpp',
            c: 'c',
            xml: 'xml',
            yaml: 'yaml',
            yml: 'yaml',
            md: 'markdown',
            plaintext: 'plaintext',
            text: 'plaintext',
        };
        const LANG_LABELS = {
            php: 'PHP',
            javascript: 'JavaScript',
            typescript: 'TypeScript',
            xml: 'HTML/XML',
            css: 'CSS',
            json: 'JSON',
            sql: 'SQL',
            bash: 'Shell',
            python: 'Python',
            ruby: 'Ruby',
            java: 'Java',
            go: 'Go',
            rust: 'Rust',
            cpp: 'C++',
            c: 'C',
            yaml: 'YAML',
            markdown: 'Markdown',
            plaintext: 'Plain Text',
        };

        let _codeBlockId = 0;

        function render(raw, highlight = false) {
            return raw.split(/(```[\s\S]*?```)/g).map((seg, i) => {
                if (i % 2 === 1) {
                    const match = seg.match(/^```(\w*)\n?([\s\S]*?)```$/);
                    const langRaw = (match?.[1] || '').toLowerCase();
                    const code = match?.[2] ?? seg.replace(/^```\w*\n?/, '').replace(/\n?```$/, '');
                    const hljsLang = LANG_MAP[langRaw] || (langRaw || null);
                    const label = LANG_LABELS[hljsLang] || LANG_LABELS[langRaw] ||
                        (langRaw ? langRaw.charAt(0).toUpperCase() + langRaw.slice(1) : 'Code');
                    const id = 'cb' + (++_codeBlockId);

                    let bodyHtml;
                    if (highlight && typeof hljs !== 'undefined') {
                        // Highlighted: wrap in <pre><code class="hljs"> so our token CSS applies
                        let highlighted;
                        try {
                            highlighted = hljsLang && hljs.getLanguage(hljsLang) ?
                                hljs.highlight(code, {
                                    language: hljsLang,
                                    ignoreIllegals: true
                                }).value :
                                hljs.highlightAuto(code).value;
                        } catch {
                            highlighted = esc(code);
                        }
                        bodyHtml = `<pre><code class="hljs">${highlighted}</code></pre>`;
                    } else {
                        // Plain — safe during streaming or if hljs not loaded
                        bodyHtml = `<pre class="plain">${esc(code)}</pre>`;
                    }

                    return `<div class="code-block">
        <div class="code-header">
          <span class="code-lang">${esc(label)}</span>
          <button class="code-copy" onclick="copyCode('${id}',this)" title="Copy code">
            <i class="bi bi-clipboard"></i>Copy
          </button>
        </div>
        <div class="code-body" id="${id}">${bodyHtml}</div>
      </div>`;
                }
                return esc(seg)
                    .replace(/`([^`\n]+?)`/g, '<code>$1</code>')
                    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n/g, '<br>');
            }).join('');
        }

        function copyCode(id, btn) {
            const body = $(id);
            if (!body) return;
            const codeEl = body.querySelector('code') || body.querySelector('pre') || body;
            const text = codeEl.textContent ?? '';
            const finish = () => {
                btn.innerHTML = '<i class="bi bi-check-lg"></i>Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-clipboard"></i>Copy';
                    btn.classList.remove('copied');
                }, 2000);
            };
            navigator.clipboard.writeText(text).then(finish).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                } catch {}
                document.body.removeChild(ta);
                finish();
            });
        }

        let streaming = false;

        function setStreaming(on) {
            streaming = on;
            $('chatSendBtn').disabled = on;
            $('homeSendBtn').disabled = on;
            $('sDot').className = on ? 's-dot streaming' : 's-dot';
            $('sTxt').textContent = on ? 'Typing…' : 'Online';
            $('sTxt').style.color = on ? 'var(--accent)' : '';
        }

        function homeKD(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendHome();
            }
        }

        function chatKD(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChat();
            }
        }

        function sendHome() {
            const text = $('homeInput').value.trim();
            if (!text || streaming) return;
            const id = 'chat_' + Date.now();
            const title = makeTitle(text);
            activeChatId = id;
            addToHistory(id, title);
            setTopTitle(title);
            goChat();
            setTimeout(() => doSend(text), 50);
        }

        function sendChat() {
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
                        message: text,
                        chat_id: activeChatId
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
                                // Plain render while streaming — partial code breaks hljs
                                agentBubble.innerHTML = render(raw, false);
                                agentBubble.classList.add('cursor-blink');
                                scrollEnd();
                            }

                            if (msg.done === true) {
                                if (agentBubble) {
                                    agentBubble.classList.remove('cursor-blink');
                                    // Final render WITH syntax highlighting
                                    agentBubble.innerHTML = render(raw, true);
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
                toast('error', err.message || 'Connection error.');
            } finally {
                setStreaming(false);
                $('chatInput').focus();
            }
        }

        async function clearCurrentChat() {
            if (streaming) return;
            const {
                isConfirmed
            } = await Swal.fire({
                title: 'Clear this conversation?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Clear',
                confirmButtonColor: '#dc2626'
            });
            if (!isConfirmed) return;
            await clearHistoryQuiet();
            $('msgInner').innerHTML = '';
            _codeBlockId = 0; // Reset code block ID counter to prevent conflicts
            addSysMsg('Conversation cleared');
        }

        async function clearHistoryQuiet(chatId = null) {
            try {
                await fetch('/ai/clear-history', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        chat_id: chatId || activeChatId
                    }),
                });
            } catch {}
        }

        async function boot() {
            loadHistory();
            renderSB();
            renderSugg('framework');

            // Don't automatically load chat history on boot since we support multiple chats
            // User should click on a chat to load it
        }

        function addBubble(content, role) {
            const inner = $('msgInner');
            const row = document.createElement('div');
            row.className = 'msg-row fade-in ' + (role === 'user' ? 'user-row' : '');

            const col = document.createElement('div');
            col.className = 'msg-col';

            const bub = document.createElement('div');
            bub.className = 'bub ' + (role === 'user' ? 'bub-user' : 'bub-agent');
            bub.innerHTML = render(content, true);

            const t = document.createElement('span');
            t.className = 'msg-time';
            t.textContent = now();

            col.appendChild(bub);
            col.appendChild(t);
            row.appendChild(col);
            inner.appendChild(row);
            scrollEnd();
            return row;
        }

        function addStreamBubble() {
            const inner = $('msgInner');
            const row = document.createElement('div');
            row.className = 'msg-row fade-in';

            const col = document.createElement('div');
            col.className = 'msg-col';

            const bub = document.createElement('div');
            bub.className = 'bub bub-agent';

            col.appendChild(bub);
            row.appendChild(col);
            inner.appendChild(row);
            scrollEnd();
            return {
                bubble: bub,
                wrap: col
            };
        }

        function addTypingDots() {
            const inner = $('msgInner');
            const row = document.createElement('div');
            row.className = 'msg-row fade-in';
            row.innerHTML = `
    <div class="bub bub-agent" style="padding:13px 16px;display:flex;gap:5px;align-items:center;">
      <span class="td"></span><span class="td"></span><span class="td"></span>
    </div>`;
            inner.appendChild(row);
            scrollEnd();
            return row;
        }

        function stamp(wrap) {
            const t = document.createElement('span');
            t.className = 'msg-time';
            t.textContent = now();
            wrap.appendChild(t);
            scrollEnd();
        }

        function addSysMsg(text) {
            const inner = $('msgInner');
            const d = document.createElement('div');
            d.className = 'sys-msg';
            d.innerHTML = `<span>${text}</span>`;
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