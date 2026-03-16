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
    <style>
        /* ══ TOKENS ══════════════════════════════════════════════════════════════════ */
        :root {
            --bg: #f4f2ee;
            --card: #ffffff;
            --bd: #e5e1da;
            --bd2: #d8d4cd;
            --ink: #181816;
            --ink2: #3a3a37;
            --muted: #8e8a83;
            --muted2: #b8b4ae;
            --accent: #1a6ef5;
            --accent-h: #1255cc;
            --accent-l: #e8f0fe;
            --accent-m: #c2d5fd;
            --green: #15803d;
            --red: #dc2626;
            --sb-open: 252px;
            --sb-closed: 54px;
            --topbar: 50px;
            --r: 10px;
            --r-lg: 16px;
        }

        /* ══ BASE ════════════════════════════════════════════════════════════════════ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            -webkit-font-smoothing: antialiased
        }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 14px;
            color: var(--ink);
            background: var(--bg)
        }

        button {
            font-family: inherit;
            cursor: pointer
        }

        textarea {
            font-family: inherit
        }

        /* ══ SCROLLBAR ════════════════════════════════════════════════════════════════ */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px
        }

        ::-webkit-scrollbar-track {
            background: transparent
        }

        ::-webkit-scrollbar-thumb {
            background: var(--bd2);
            border-radius: 4px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--muted2)
        }

        /* ══ LAYOUT ══════════════════════════════════════════════════════════════════ */
        #app {
            display: flex;
            height: 100vh;
            overflow: hidden
        }

        #main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0
        }

        /* ══════════════════════════════════════════════════════════════════════════════
   SIDEBAR — polished, collapsible, with titled history
══════════════════════════════════════════════════════════════════════════════ */
        #sidebar {
            position: relative;
            width: var(--sb-open);
            min-width: var(--sb-open);
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--card);
            border-right: 1px solid var(--bd);
            transition: width .24s cubic-bezier(.4, 0, .2, 1), min-width .24s cubic-bezier(.4, 0, .2, 1);
            overflow: hidden;
            flex-shrink: 0;
            z-index: 20;
        }

        #sidebar.collapsed {
            width: var(--sb-closed);
            min-width: var(--sb-closed);
        }

        #sidebar.collapsed {
            width: var(--sb-closed);
            min-width: var(--sb-closed);
        }

        /* ── Sidebar head ─────────────────────────────────────────────────────────── */
        .sb-head {
            height: 50px;
            display: flex;
            align-items: center;
            padding: 0 8px 0 12px;
            gap: 8px;
            flex-shrink: 0;
        }

        .sb-logo {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-h) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(26, 110, 245, .35);
            transition: transform .15s;
        }

        .sb-logo:hover {
            transform: scale(1.06)
        }

        .sb-wordmark {
            font-family: 'Instrument Serif', serif;
            font-size: 1.05rem;
            font-style: italic;
            color: var(--ink);
            white-space: nowrap;
            flex: 1;
            overflow: hidden;
            transition: opacity .18s, visibility .18s;
        }

        .sb-wordmark em {
            color: var(--accent);
            font-style: normal
        }

        #sidebar.collapsed .sb-wordmark {
            opacity: 0;
            visibility: hidden;
            flex: 0;
            width: 0
        }

        /* Toggle button always occupies its own fixed slot so it's never clipped */
        .sb-toggle {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: none;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 14px;
            transition: background .15s, color .15s;
            flex-shrink: 0;
            /* when collapsed, logo+gap+toggle = 12+30+8+28+8 = 86 → fits within 54px?
     No — so when collapsed we hide the logo too and just show toggle centred */
        }

        .sb-toggle:hover {
            background: var(--bg);
            color: var(--ink)
        }

        /* Collapsed: hide logo, center toggle */
        #sidebar.collapsed .sb-head {
            justify-content: center;
            padding: 0
        }

        #sidebar.collapsed .sb-logo {
            display: none
        }

        /* .topbar removed */

        /* ── New chat button ──────────────────────────────────────────────────────── */
        .sb-new {
            margin: 10px 10px 2px;
            height: 36px;
            border-radius: var(--r);
            border: 1.5px solid var(--bd);
            background: linear-gradient(135deg, #fafaf8 0%, #f4f2ee 100%);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 11px;
            color: var(--ink2);
            font-size: .8rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            transition: background .15s, border-color .15s, box-shadow .15s;
            flex-shrink: 0;
        }

        .sb-new:hover {
            border-color: var(--accent-m);
            background: var(--accent-l);
            box-shadow: 0 1px 4px rgba(26, 110, 245, .12);
            color: var(--accent);
        }

        .sb-new:hover .sb-new-icon {
            color: var(--accent)
        }

        .sb-new-icon {
            color: var(--muted);
            font-size: 13px;
            flex-shrink: 0;
            transition: color .15s
        }

        .sb-new-txt {
            transition: opacity .15s, max-width .2s;
            max-width: 160px;
            overflow: hidden
        }

        #sidebar.collapsed .sb-new-txt {
            opacity: 0;
            max-width: 0
        }

        #sidebar.collapsed .sb-new {
            justify-content: center;
            padding: 0;
            margin-left: 9px;
            margin-right: 9px
        }

        /* ── Section label ────────────────────────────────────────────────────────── */
        .sb-label {
            font-size: .62rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted2);
            padding: 12px 14px 4px;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity .15s;
            flex-shrink: 0;
        }

        #sidebar.collapsed .sb-label {
            opacity: 0
        }

        /* ── Chat list ────────────────────────────────────────────────────────────── */
        .sb-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 8px 8px
        }

        .sb-empty {
            padding: 20px 8px;
            text-align: center;
            font-size: .75rem;
            color: var(--muted2);
        }

        .sb-empty i {
            display: block;
            font-size: 22px;
            margin-bottom: 8px;
            opacity: .4
        }

        .sb-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 8px;
            border-radius: var(--r);
            white-space: nowrap;
            overflow: hidden;
            transition: background .12s;
            position: relative;
            user-select: none;
        }

        .sb-item:hover {
            background: var(--bg)
        }

        .sb-item.active {
            background: var(--accent-l);
        }

        .sb-item-ico {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 11px;
            background: var(--bg);
            color: var(--muted);
            transition: background .12s, color .12s;
        }

        .sb-item.active .sb-item-ico {
            background: var(--accent-m);
            color: var(--accent)
        }

        .sb-item-lbl {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: .78rem;
            color: var(--ink2);
            transition: opacity .15s;
        }

        .sb-item.active .sb-item-lbl {
            color: var(--accent);
            font-weight: 500
        }

        #sidebar.collapsed .sb-item-lbl {
            opacity: 0;
            width: 0
        }

        #sidebar.collapsed .sb-item {
            justify-content: center
        }

        .sb-item-del {
            width: 20px;
            height: 20px;
            border-radius: 5px;
            border: none;
            background: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted2);
            font-size: 11px;
            opacity: 0;
            transition: opacity .12s, background .12s, color .12s;
            flex-shrink: 0;
        }

        .sb-item:hover .sb-item-del {
            opacity: 1
        }

        .sb-item-del:hover {
            background: #fef2f2;
            color: var(--red)
        }

        #sidebar.collapsed .sb-item-del {
            display: none
        }

        /* Date group labels */
        .sb-date-group {
            font-size: .62rem;
            color: var(--muted2);
            padding: 10px 8px 3px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity .15s;
        }

        #sidebar.collapsed .sb-date-group {
            opacity: 0
        }

        /* ── Sidebar footer ────────────────────────────────────────────────────────── */
        .sb-foot {
            border-top: 1px solid var(--bd);
            padding: 6px 8px 8px;
            display: flex;
            flex-direction: column;
            gap: 1px;
            flex-shrink: 0;
        }

        .sb-foot-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 7px 8px;
            border-radius: var(--r);
            background: none;
            border: none;
            color: var(--muted);
            font-size: .78rem;
            white-space: nowrap;
            overflow: hidden;
            transition: background .12s, color .12s;
            width: 100%;
            text-align: left;
        }

        .sb-foot-btn:hover {
            background: var(--bg);
            color: var(--ink)
        }

        .sb-foot-btn.danger:hover {
            background: #fef2f2;
            color: var(--red)
        }

        .sb-foot-btn i {
            font-size: 14px;
            flex-shrink: 0;
            width: 22px;
            text-align: center
        }

        .sb-foot-lbl {
            transition: opacity .15s;
            overflow: hidden;
            white-space: nowrap
        }

        #sidebar.collapsed .sb-foot-lbl {
            opacity: 0;
            width: 0
        }

        #sidebar.collapsed .sb-foot-btn {
            justify-content: center
        }

        #sidebar.collapsed .sb-foot-btn i {
            width: auto
        }

        /* .topbar / .mainbar removed */

        /* Status row in sidebar footer */
        .sb-status {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 7px 8px;
            border-radius: var(--r);
            font-size: .78rem;
            color: var(--muted);
            white-space: nowrap;
            overflow: hidden;
        }

        #sidebar.collapsed .sb-status {
            justify-content: center
        }

        .status-badge {
            display: none
        }

        /* kept for JS compat but hidden */

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .68rem;
            font-weight: 500;
            padding: 3px 9px;
            border-radius: 9999px;
            background: var(--bg);
            border: 1px solid var(--bd);
            color: var(--muted);
            transition: color .2s;
        }

        .s-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            flex-shrink: 0;
        }

        .s-dot.streaming {
            background: var(--accent);
            animation: sdot .7s infinite
        }

        @keyframes sdot {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .2
            }
        }

        .icon-btn {
            width: 30px;
            height: 30px;
            border-radius: var(--r);
            background: none;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 13px;
            transition: background .12s, color .12s;
        }

        .icon-btn:hover {
            background: var(--bg);
            color: var(--ink)
        }

        .icon-btn.danger:hover {
            background: #fef2f2;
            color: var(--red)
        }

        /* ══════════════════════════════════════════════════════════════════════════════
   HOME VIEW
══════════════════════════════════════════════════════════════════════════════ */
        #homeView {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px 48px;
            background: var(--bg);
        }

        #homeView.gone {
            display: none
        }

        .home-inner {
            width: 100%;
            max-width: 628px;
            animation: fadeUp .35s ease forwards
        }

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

        .home-brand {
            text-align: center;
            margin-bottom: 30px
        }

        .home-brand h1 {
            font-family: 'Instrument Serif', serif;
            font-size: 2.7rem;
            font-weight: 400;
            letter-spacing: -.025em;
            line-height: 1.05;
            color: var(--ink);
        }

        .home-brand h1 em {
            font-style: italic;
            color: var(--accent)
        }

        .home-brand p {
            font-size: .79rem;
            color: var(--muted);
            margin-top: 7px;
            letter-spacing: .01em
        }

        /* ── Search box ─────────────────────────────────────────────────────────── */
        .search-box {
            background: var(--card);
            border: 1.5px solid var(--bd);
            border-radius: var(--r-lg);
            box-shadow: 0 2px 16px rgba(0, 0, 0, .07), 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
            transition: border-color .2s, box-shadow .2s;
        }

        .search-box:focus-within {
            border-color: var(--accent-m);
            box-shadow: 0 0 0 4px rgba(26, 110, 245, .09), 0 2px 16px rgba(0, 0, 0, .07);
        }

        .search-box textarea {
            display: block;
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            resize: none;
            font-size: .9rem;
            color: var(--ink);
            line-height: 1.55;
            min-height: 28px;
            max-height: 140px;
            overflow-y: auto;
            padding: 16px 18px 4px;
        }

        .search-box textarea::placeholder {
            color: var(--muted2)
        }

        .search-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px 10px;
        }

        /* ── Category tabs ──────────────────────────────────────────────────────── */
        .cat-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 14px
        }

        .cat-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .71rem;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 9999px;
            border: 1.5px solid var(--bd);
            background: var(--card);
            color: var(--muted);
            transition: all .14s;
            white-space: nowrap;
        }

        .cat-btn i {
            font-size: 10px
        }

        .cat-btn:hover {
            background: #edeae5;
            color: var(--ink);
            border-color: var(--bd2)
        }

        .cat-btn.active {
            background: var(--ink);
            color: #fff;
            border-color: var(--ink)
        }

        /* ── Suggestion block ───────────────────────────────────────────────────── */
        .sugg-block {
            margin-top: 10px;
            background: var(--card);
            border: 1.5px solid var(--bd);
            border-radius: var(--r-lg);
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .04);
        }

        .sugg-row {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            text-align: left;
            padding: 11px 16px;
            font-size: .83rem;
            color: var(--ink2);
            background: none;
            border: none;
            transition: background .1s;
        }

        .sugg-row+.sugg-row {
            border-top: 1px solid var(--bd)
        }

        .sugg-row:hover {
            background: #f0ede8
        }

        .sugg-row:hover .sugg-arrow {
            color: var(--accent);
            transform: translateX(2px) translateY(-2px)
        }

        .sugg-arrow {
            font-size: 10px;
            color: var(--muted2);
            flex-shrink: 0;
            transition: color .12s, transform .12s
        }

        /* ══════════════════════════════════════════════════════════════════════════════
   CHAT VIEW
══════════════════════════════════════════════════════════════════════════════ */
        #chatView {
            flex: 1;
            display: none;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg)
        }

        #chatView.active {
            display: flex
        }

        /* Messages */
        #chatMessages {
            flex: 1;
            overflow-y: auto;
            padding: 28px 20px
        }

        #msgInner {
            max-width: 700px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 4px
        }

        /* ── Message rows ───────────────────────────────────────────────────────── */
        .msg-row {
            display: flex;
            align-items: flex-end;
            padding: 2px 0
        }

        .msg-row.user-row {
            flex-direction: row-reverse
        }

        .msg-col {
            display: flex;
            flex-direction: column;
            gap: 3px;
            max-width: 80%
        }

        .user-row .msg-col {
            align-items: flex-end;
            margin-left: auto
        }

        /* ── Bubble styles ──────────────────────────────────────────────────────── */
        .bub {
            padding: 11px 15px;
            font-size: .875rem;
            line-height: 1.7;
            word-break: break-word;
            border-radius: var(--r-lg);
            animation: bubIn .18s ease forwards;
        }

        @keyframes bubIn {
            from {
                opacity: 0;
                transform: translateY(5px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .bub-agent {
            background: var(--card);
            border: 1px solid var(--bd);
            border-bottom-left-radius: 3px;
            color: var(--ink);
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .bub-user {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-h) 100%);
            color: #fff;
            border-bottom-right-radius: 3px;
            box-shadow: 0 2px 8px rgba(26, 110, 245, .28);
        }

        .msg-time {
            font-size: .63rem;
            color: var(--muted2);
            padding: 0 2px;
            white-space: nowrap
        }

        .user-row .msg-time {
            text-align: right
        }

        /* Code inside bubbles — Claude-style blocks */
        .bub pre {
            margin: 10px 0;
            border-radius: 10px;
            overflow: hidden;
            background: none;
            padding: 0;
            font-size: inherit
        }

        .code-block {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #2e3349;
            background: #1a1d2e
        }

        .code-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1e2235;
            padding: 8px 14px;
            border-bottom: 1px solid #2e3349;
        }

        .code-lang {
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #7b8ab8;
            font-family: 'DM Mono', monospace;
        }

        .code-copy {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .7rem;
            color: #7b8ab8;
            background: none;
            border: 1px solid #2e3349;
            border-radius: 5px;
            padding: 3px 9px;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
            font-family: 'DM Sans', sans-serif;
        }

        .code-copy:hover {
            background: #2a2f47;
            color: #a8b4d8;
            border-color: #3d4560
        }

        .code-copy.copied {
            color: #4ade80;
            border-color: #166534
        }

        .code-copy i {
            font-size: 11px
        }

        .code-body {
            padding: 14px 16px;
            overflow-x: auto;
            font-family: 'DM Mono', monospace;
            font-size: .78rem;
            line-height: 1.65;
            color: #c8d3ed;
            white-space: pre;
        }

        /* Inline code */
        .bub code {
            font-family: 'DM Mono', monospace;
            font-size: .78rem;
            background: rgba(26, 110, 245, .08);
            color: var(--accent);
            padding: 1px 5px;
            border-radius: 4px
        }

        .bub-user code {
            background: rgba(255, 255, 255, .18);
            color: #fff
        }

        .bub strong {
            font-weight: 600
        }

        /* Streaming cursor */
        .cursor-blink::after {
            content: '▋';
            animation: blink .6s step-end infinite;
            color: var(--accent);
            font-size: .85em;
            margin-left: 1px
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

        /* Typing dots */
        @keyframes td {

            0%,
            60%,
            100% {
                transform: translateY(0);
                opacity: .35
            }

            30% {
                transform: translateY(-5px);
                opacity: 1
            }
        }

        .td {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--muted);
            display: inline-block
        }

        .td:nth-child(1) {
            animation: td 1.1s infinite
        }

        .td:nth-child(2) {
            animation: td 1.1s .17s infinite
        }

        .td:nth-child(3) {
            animation: td 1.1s .34s infinite
        }

        /* System message */
        .sys-msg {
            display: flex;
            justify-content: center;
            padding: 6px 0
        }

        .sys-msg span {
            font-size: .68rem;
            color: var(--muted);
            background: rgba(255, 255, 255, .7);
            border: 1px solid var(--bd);
            border-radius: 9999px;
            padding: 3px 12px;
            backdrop-filter: blur(4px);
        }

        /* ── Chat input area ────────────────────────────────────────────────────── */
        #chatInputArea {
            padding: 10px 20px 18px;
            flex-shrink: 0;
            background: var(--bg);
        }

        .chat-input-wrap {
            max-width: 700px;
            margin: 0 auto
        }

        .chat-box {
            background: var(--card);
            border: 1.5px solid var(--bd);
            border-radius: var(--r-lg);
            box-shadow: 0 2px 16px rgba(0, 0, 0, .07), 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
            transition: border-color .2s, box-shadow .2s;
        }

        .chat-box:focus-within {
            border-color: var(--accent-m);
            box-shadow: 0 0 0 4px rgba(26, 110, 245, .09), 0 2px 16px rgba(0, 0, 0, .07);
        }

        .chat-box textarea {
            display: block;
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            resize: none;
            font-size: .875rem;
            color: var(--ink);
            line-height: 1.55;
            min-height: 26px;
            max-height: 130px;
            overflow-y: auto;
            padding: 14px 16px 4px;
        }

        .chat-box textarea::placeholder {
            color: var(--muted2)
        }

        .chat-box-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px 9px;
        }

        /* Model chip */
        .model-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .68rem;
            color: var(--muted);
            background: var(--bg);
            border: 1px solid var(--bd);
            border-radius: 9999px;
            padding: 3px 10px;
        }

        .model-chip i {
            color: var(--accent);
            font-size: 10px
        }

        /* Send button */
        .send-btn {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--ink);
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
            transition: background .15s, transform .1s, box-shadow .15s;
        }

        .send-btn:hover:not(:disabled) {
            background: var(--accent);
            transform: scale(1.05);
            box-shadow: 0 3px 10px rgba(26, 110, 245, .35);
        }

        .send-btn:disabled {
            background: var(--bd);
            color: var(--muted2);
            transform: none;
            cursor: not-allowed
        }

        .hint {
            font-size: .63rem;
            color: var(--muted2);
            text-align: center;
            margin-top: 6px
        }

        /* ── Misc animations ────────────────────────────────────────────────────── */
        @keyframes fadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        .fade-in {
            animation: fadeIn .18s ease forwards
        }
    </style>
</head>

<body>
    <div id="app">

        <!-- ════════════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════════════ -->
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
                <button class="sb-foot-btn danger" onclick="clearCurrentChat()" title="Clear current chat">
                    <i class="bi bi-eraser"></i>
                    <span class="sb-foot-lbl">Clear chat</span>
                </button>
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

        <!-- ════════════════════════════════════════════════════════════
     MAIN COLUMN
════════════════════════════════════════════════════════════ -->
        <div id="main">

            <!-- ════ HOME VIEW ════ -->
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

            <!-- ════ CHAT VIEW ════ -->
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

        </div><!-- /main -->
    </div><!-- /app -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        'use strict';

        // ─── Helpers ──────────────────────────────────────────────────────────────────
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

        // ─── Sidebar collapse ─────────────────────────────────────────────────────────
        // State lives in the DOM — never drifts out of sync with a JS variable
        function toggleSB() {
            const isNowCollapsed = $('sidebar').classList.toggle('collapsed');
            $('sbToggleIcon').className = isNowCollapsed ?
                'bi bi-layout-sidebar' // collapsed → show "open" icon
                :
                'bi bi-layout-sidebar-reverse'; // expanded  → show "close" icon
        }

        // ─── Chat history (localStorage) ─────────────────────────────────────────────
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

        // Group by Today / Yesterday / Older
        function renderSB() {
            const list = $('sbList');
            if (!chatHistory.length) {
                list.innerHTML = `<div class="sb-empty"><i class="bi bi-chat-square-dots"></i>No conversations yet</div>`;
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

            // Attach handlers safely (no inline onclick with string data)
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
            goChat();
            try {
                const r = await fetch('/ai/history');
                const d = await r.json();
                if (d.success && d.messages?.length) {
                    d.messages.filter(m => m.role !== 'system')
                        .forEach(m => addBubble(m.content, m.role === 'user' ? 'user' : 'agent'));
                    addSysMsg('Conversation restored');
                }
            } catch {}
        }

        async function deleteChat(id) {
            chatHistory = chatHistory.filter(c => c.id !== id);
            saveHistory();
            if (activeChatId === id) {
                await clearHistoryQuiet();
                activeChatId = null;
                $('msgInner').innerHTML = '';
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
            goHome();
            setTopTitle(null);
            renderSB();
            toast('success', 'All history cleared');
        }

        function setTopTitle(_title) {
            /* no topbar — title lives in sidebar active item */ }

        // ─── Suggestion data ──────────────────────────────────────────────────────────
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

        // ─── View switch ──────────────────────────────────────────────────────────────
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
            await clearHistoryQuiet();
            activeChatId = null;
            $('msgInner').innerHTML = '';
            setTopTitle(null);
            renderSB();
            goHome();
        }

        // ─── Render / escape ──────────────────────────────────────────────────────────
        function esc(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Map common language aliases → display labels
        const LANG_LABELS = {
            php: 'PHP',
            js: 'JavaScript',
            javascript: 'JavaScript',
            ts: 'TypeScript',
            typescript: 'TypeScript',
            html: 'HTML',
            css: 'CSS',
            json: 'JSON',
            sql: 'SQL',
            bash: 'Bash',
            sh: 'Shell',
            python: 'Python',
            py: 'Python',
            ruby: 'Ruby',
            java: 'Java',
            go: 'Go',
            rust: 'Rust',
            cpp: 'C++',
            c: 'C',
            xml: 'XML',
            yaml: 'YAML',
            yml: 'YAML',
            md: 'Markdown',
            plaintext: 'Plain Text',
            text: 'Plain Text',
            '': (l => l || 'Code')
        };

        let _codeBlockId = 0;

        function render(raw) {
            return raw.split(/(```[\s\S]*?```)/g).map((seg, i) => {
                if (i % 2 === 1) {
                    // Extract language from fence opener
                    const match = seg.match(/^```(\w*)\n?([\s\S]*?)```$/);
                    const lang = (match?.[1] || '').toLowerCase();
                    const code = match?.[2] ?? seg.replace(/^```\w*\n?/, '').replace(/\n?```$/, '');
                    const label = LANG_LABELS[lang] || (lang ? lang.charAt(0).toUpperCase() + lang.slice(1) : 'Code');
                    const id = 'cb' + (++_codeBlockId);
                    return `<div class="code-block">
        <div class="code-header">
          <span class="code-lang">${esc(label)}</span>
          <button class="code-copy" onclick="copyCode('${id}',this)" title="Copy code">
            <i class="bi bi-clipboard"></i> Copy
          </button>
        </div>
        <div class="code-body" id="${id}">${esc(code)}</div>
      </div>`;
                }
                // Plain text segment
                return esc(seg)
                    .replace(/`([^`\n]+?)`/g, '<code>$1</code>')
                    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n/g, '<br>');
            }).join('');
        }

        // Copy handler
        function copyCode(id, btn) {
            const el = $(id);
            if (!el) return;
            navigator.clipboard.writeText(el.textContent).then(() => {
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                    btn.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                // Fallback for non-HTTPS
                const ta = document.createElement('textarea');
                ta.value = el.textContent;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                    btn.classList.remove('copied');
                }, 2000);
            });
        }

        // ─── Streaming state ──────────────────────────────────────────────────────────
        let streaming = false;

        function setStreaming(on) {
            streaming = on;
            $('chatSendBtn').disabled = on;
            $('homeSendBtn').disabled = on;
            $('sDot').className = on ? 's-dot streaming' : 's-dot';
            $('sTxt').textContent = on ? 'Typing…' : 'Online';
            $('sTxt').style.color = on ? 'var(--accent)' : '';
        }

        // ─── Send ─────────────────────────────────────────────────────────────────────
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
                toast('error', err.message || 'Connection error.');
            } finally {
                setStreaming(false);
                $('chatInput').focus();
            }
        }

        // ─── Clear ────────────────────────────────────────────────────────────────────
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
            addSysMsg('Conversation cleared');
        }

        async function clearHistoryQuiet() {
            try {
                await fetch('/ai/clear-history', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({}),
                });
            } catch {}
        }

        // ─── Boot ─────────────────────────────────────────────────────────────────────
        async function boot() {
            loadHistory();
            renderSB();
            renderSugg('framework');

            try {
                const r = await fetch('/ai/history');
                const d = await r.json();
                if (d.success && d.messages?.length) {
                    const msgs = d.messages.filter(m => m.role !== 'system');
                    if (msgs.length) {
                        if (chatHistory.length) {
                            activeChatId = chatHistory[0].id;
                            setTopTitle(chatHistory[0].title);
                            renderSB();
                        }
                        goChat();
                        msgs.forEach(m => addBubble(m.content, m.role === 'user' ? 'user' : 'agent'));
                        addSysMsg('Previous conversation restored');
                        return;
                    }
                }
            } catch {}
        }

        // ─── UI primitives ────────────────────────────────────────────────────────────
        function addBubble(content, role) {
            const inner = $('msgInner');
            const row = document.createElement('div');
            row.className = 'msg-row fade-in ' + (role === 'user' ? 'user-row' : '');

            const col = document.createElement('div');
            col.className = 'msg-col';

            const bub = document.createElement('div');
            bub.className = 'bub ' + (role === 'user' ? 'bub-user' : 'bub-agent');
            bub.innerHTML = render(content);

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