<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LavaLust</title>
    <link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700;800&family=Unbounded:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --lava: #dd4814;
            --lava-dim: #b83a10;
            --lava-glow: rgba(221,72,20,0.15);
            --lava-glow-strong: rgba(221,72,20,0.25);
            --bg: #0a0a0b;
            --bg2: #111113;
            --bg3: #18181b;
            --border: rgba(255,255,255,0.07);
            --border-hot: rgba(221,72,20,0.35);
            --text: #f4f4f5;
            --text-muted: #71717a;
            --text-dim: #3f3f46;
            --mono: 'JetBrains Mono', monospace;
            --sans: 'Unbounded', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── NOISE TEXTURE ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
            opacity: 0.6;
        }

        /* ── GRID BACKGROUND ── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: 0;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, black 30%, transparent 100%);
        }

        /* ── GLOW ORBS ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 {
            width: 600px; height: 600px;
            top: -200px; left: -100px;
            background: radial-gradient(circle, rgba(221,72,20,0.12) 0%, transparent 70%);
        }
        .orb-2 {
            width: 400px; height: 400px;
            top: 200px; right: -100px;
            background: radial-gradient(circle, rgba(221,72,20,0.07) 0%, transparent 70%);
        }

        /* ── LAYOUT ── */
        .wrap {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* ── NAV ── */
        nav {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            background: rgba(10,10,11,0.6);
            max-width: 100%;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text);
            text-decoration: none;
        }

        .nav-logo .flame {
            width: 28px; height: 28px;
            background: var(--lava);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px var(--lava-glow-strong);
            flex-shrink: 0;
        }

        .nav-logo .flame svg {
            width: 15px;
            height: 15px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            transition: color 0.2s, background 0.2s;
        }

        .nav-links a:hover { color: var(--text); background: var(--bg3); }

        .nav-links .btn-nav {
            color: var(--text);
            background: var(--lava);
            padding: 0.4rem 1rem;
            border-radius: 6px;
            margin-left: 0.5rem;
            transition: background 0.2s, box-shadow 0.2s;
        }

        .nav-links .btn-nav:hover {
            background: var(--lava-dim);
            box-shadow: 0 0 20px var(--lava-glow-strong);
        }

        /* ── HERO ── */
        .hero {
            padding: 7rem 2rem 5rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(221,72,20,0.1);
            border: 1px solid var(--border-hot);
            color: #f97316;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            margin-bottom: 2rem;
            font-family: var(--mono);
        }

        .badge::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--lava);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--lava);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 8px var(--lava); }
            50% { opacity: 0.5; box-shadow: 0 0 3px var(--lava); }
        }

        .hero h1 {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            margin-bottom: 1.5rem;
        }

        .hero h1 .word-lava { color: var(--lava); }
        .hero h1 .word-lust {
            color: transparent;
            -webkit-text-stroke: 1.5px rgba(255,255,255,0.3);
        }

        .hero-sub {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 520px;
            margin: 0 auto 2.5rem;
            line-height: 1.7;
            font-weight: 400;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-family: var(--sans);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--lava);
            color: #fff;
            box-shadow: 0 0 0 0 var(--lava-glow);
        }

        .btn-primary:hover {
            background: var(--lava-dim);
            box-shadow: 0 0 30px var(--lava-glow-strong), 0 4px 15px rgba(0,0,0,0.3);
            transform: translateY(-1px);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            color: var(--text);
            border-color: rgba(255,255,255,0.2);
            background: var(--bg3);
        }

        /* ── STAT BAR ── */
        .stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            flex-wrap: wrap;
            padding: 3rem 2rem;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            position: relative;
            z-index: 1;
        }

        .stat { text-align: center; }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .stat-value span { color: var(--lava); }

        .stat-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ── SECTION ── */
        section {
            padding: 5rem 2rem;
            position: relative;
            z-index: 1;
        }

        .section-label {
            font-family: var(--mono);
            font-size: 0.72rem;
            font-weight: 500;
            color: var(--lava);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 0.75rem;
        }

        .section-title {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-bottom: 1rem;
        }

        .section-desc {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
            max-width: 480px;
        }

        /* ── FEATURES GRID ── */
        .features-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-top: 3rem;
        }

        .feature {
            background: var(--bg);
            padding: 2rem;
            transition: background 0.2s;
            position: relative;
        }

        .feature:hover { background: var(--bg2); }

        .feature::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--lava-glow-strong), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .feature:hover::before { opacity: 1; }

        .feature-icon {
            width: 40px; height: 40px;
            background: rgba(221,72,20,0.1);
            border: 1px solid var(--border-hot);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .feature-icon svg {
            width: 18px;
            height: 18px;
            stroke: var(--lava);
            fill: none;
            stroke-width: 1.6;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .feature h3 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }

        .feature p {
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* ── CODE SECTION ── */
        .code-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .code-block {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .code-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg3);
        }

        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot-r { background: #ff5f57; }
        .dot-y { background: #febc2e; }
        .dot-g { background: #28c840; }

        .code-filename {
            font-family: var(--mono);
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-left: 0.5rem;
        }

        .code-body {
            padding: 1.5rem;
            font-family: var(--mono);
            font-size: 0.82rem;
            line-height: 1.8;
            color: #a1a1aa;
            overflow-x: auto;
        }

        .code-body .kw { color: #f97316; }
        .code-body .fn { color: #60a5fa; }
        .code-body .str { color: #86efac; }
        .code-body .cm { color: #3f3f46; }
        .code-body .cl { color: #fde68a; }
        .code-body .var { color: #c4b5fd; }

        /* ── STRUCTURE ── */
        .structure-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .dir-item {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.875rem 1rem;
            font-family: var(--mono);
            font-size: 0.8rem;
            color: var(--text-muted);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .dir-item:hover {
            border-color: var(--border-hot);
            color: var(--text);
            background: rgba(221,72,20,0.05);
        }

        .dir-item .dir-icon {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dir-item .dir-icon svg {
            width: 14px;
            height: 14px;
            stroke: var(--lava);
            fill: none;
            stroke-width: 1.6;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0.8;
        }

        .dir-item:hover .dir-icon svg { opacity: 1; }

        /* ── FOOTER ── */
        footer {
            border-top: 1px solid var(--border);
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .footer-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-meta {
            font-family: var(--mono);
            font-size: 0.75rem;
            color: var(--text-dim);
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .footer-meta span { color: var(--text-muted); }

        .footer-links {
            display: flex;
            gap: 1rem;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.82rem;
            transition: color 0.2s;
        }

        .footer-links a:hover { color: var(--lava); }

        /* ── DIVIDER ── */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin: 0 2rem;
            position: relative;
            z-index: 1;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero > * {
            animation: fadeUp 0.6s ease both;
        }

        .hero .badge         { animation-delay: 0.05s; }
        .hero h1             { animation-delay: 0.15s; }
        .hero .hero-sub      { animation-delay: 0.25s; }
        .hero .hero-actions  { animation-delay: 0.35s; }

        @media (max-width: 768px) {
            .features-layout { grid-template-columns: 1fr; }
            .code-section { grid-template-columns: 1fr; }
            nav { padding: 1rem 1.5rem; }
            .nav-links a:not(.btn-nav) { display: none; }
            section { padding: 3rem 1.5rem; }
        }
    </style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<!-- NAV -->
<nav>
    <a class="nav-logo" href="#">
        <div class="flame">
            <!-- Flame SVG -->
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 21C12 21 5 17 5 11C5 8 7.5 5.5 10 5.5C10 5.5 9 8 11 9C11 9 10 6.5 13.5 5C13.5 5 12.5 9 15.5 9C17.5 9 18.5 7 18.5 7C18.5 7 21 9.5 19 14C17.5 17.5 12 21 12 21Z" fill="white" opacity="0.95"/>
            </svg>
        </div>
        LavaLust
    </a>
    <div class="nav-links">
        <a href="https://lavalust.netlify.app/docs/" target="_blank">Docs</a>
        <a href="https://github.com/ronmarasigan/LavaLust" target="_blank">GitHub</a>
        <a href="https://lavalust.netlify.app/docs/" target="_blank" class="btn-nav">Get Started</a>
    </div>
</nav>

<!-- HERO -->
<div class="hero wrap">
    <div class="badge">v<?php echo config_item('VERSION') ?? '4.x'; ?> — Now Available</div>
    <h1>
        <span class="word-lava">Lava</span><span class="word-lust">Lust</span><br>Framework
    </h1>
    <p class="hero-sub">
        A lightweight, expressive PHP MVC framework built for developers who want structure without the bloat.
    </p>
    <div class="hero-actions">
        <a href="https://lavalust.netlify.app/docs/" target="_blank" class="btn btn-primary">
            Read the Docs
        </a>
        <a href="https://github.com/ronmarasigan/LavaLust" target="_blank" class="btn btn-ghost">
            View on GitHub
        </a>
    </div>
</div>

<!-- STATS -->
<div class="stats">
    <div class="stat">
        <div class="stat-value">MVC<span>+</span></div>
        <div class="stat-label">Architecture</div>
    </div>
    <div class="stat">
        <div class="stat-value"><span>4</span> DB</div>
        <div class="stat-label">Drivers</div>
    </div>
    <div class="stat">
        <div class="stat-value">HMVC<span>✓</span></div>
        <div class="stat-label">Module Support</div>
    </div>
    <div class="stat">
        <div class="stat-value">REST<span>*</span></div>
        <div class="stat-label">API Ready</div>
    </div>
</div>

<div class="divider"></div>

<!-- FEATURES -->
<section>
    <div class="wrap">
        <div class="section-label">// features</div>
        <h2 class="section-title">Everything you need.<br>Nothing you don't.</h2>
        <p class="section-desc">LavaLust gives you a clean, consistent structure so you can focus on building — not configuring.</p>

        <div class="features-layout">

            <!-- MVC -->
            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="5" rx="1"/><rect x="2" y="10" width="9" height="5" rx="1"/><rect x="2" y="17" width="9" height="4" rx="1"/><path d="M15 12h7M15 17h7M19 10v10"/></svg>
                </div>
                <h3>MVC Architecture</h3>
                <p>Clean separation between Models, Views, and Controllers keeps your codebase maintainable as it grows.</p>
            </div>

            <!-- Routing -->
            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><circle cx="5" cy="6" r="2"/><circle cx="19" cy="6" r="2"/><circle cx="12" cy="18" r="2"/><path d="M7 6h10M14 8l4 8M10 8L6 16"/></svg>
                </div>
                <h3>Flexible Routing</h3>
                <p>Define routes with GET, POST, PUT, DELETE and more. Supports named routes, closures, and grouped prefixes.</p>
            </div>

            <!-- ORM -->
            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v4c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 9v5c0 1.66 4 3 9 3s9-1.34 9-3V9"/><path d="M3 14v5c0 1.66 4 3 9 3s9-1.34 9-3v-5"/></svg>
                </div>
                <h3>ORM-style Models</h3>
                <p>Fluent query builder with relationships, soft deletes, timestamps, mass assignment protection, and eager loading.</p>
            </div>

            <!-- HMVC -->
            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><rect x="2" y="2" width="8" height="8" rx="1.5"/><rect x="14" y="2" width="8" height="8" rx="1.5"/><rect x="2" y="14" width="8" height="8" rx="1.5"/><rect x="14" y="14" width="8" height="8" rx="1.5"/></svg>
                </div>
                <h3>HMVC Modules</h3>
                <p>Scale your app with self-contained modules. Each module owns its controllers, models, and views.</p>
            </div>

            <!-- REST -->
            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 12h16M12 4l8 8-8 8"/></svg>
                </div>
                <h3>REST API Support</h3>
                <p>Build JSON APIs out of the box using built-in conventions, response helpers, and content negotiation.</p>
            </div>

            <!-- Libraries -->
            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 19V6a2 2 0 0 1 2-2h13a1 1 0 0 1 1 1v13"/><path d="M4 19a2 2 0 0 0 2 2h13a1 1 0 0 0 1-1v-1"/><path d="M8 7h6M8 11h8"/></svg>
                </div>
                <h3>Libraries &amp; Helpers</h3>
                <p>Sessions, form validation, file uploads, pagination, encryption — batteries included where it counts.</p>
            </div>

        </div>
    </div>
</section>

<div class="divider"></div>

<!-- CODE EXAMPLE -->
<section>
    <div class="wrap">
        <div class="code-section">
            <div>
                <div class="section-label">// quick start</div>
                <h2 class="section-title">Up and running in minutes.</h2>
                <p class="section-desc">Define a route, write a controller method, render a view. That's the whole loop.</p>
            </div>

            <div>
                <div class="code-block" style="margin-bottom:1rem;">
                    <div class="code-header">
                        <div class="dot dot-r"></div>
                        <div class="dot dot-y"></div>
                        <div class="dot dot-g"></div>
                        <span class="code-filename">app/config/routes.php</span>
                    </div>
                    <div class="code-body">
<span class="var">$router</span>-><span class="fn">get</span>(<span class="str">'/'</span>, <span class="str">'Welcome::index'</span>);<br>
<span class="var">$router</span>-><span class="fn">get</span>(<span class="str">'/users'</span>, <span class="str">'Users::index'</span>);<br>
<span class="var">$router</span>-><span class="fn">post</span>(<span class="str">'/users/store'</span>, <span class="str">'Users::store'</span>);
                    </div>
                </div>

                <div class="code-block">
                    <div class="code-header">
                        <div class="dot dot-r"></div>
                        <div class="dot dot-y"></div>
                        <div class="dot dot-g"></div>
                        <span class="code-filename">app/controllers/Welcome.php</span>
                    </div>
                    <div class="code-body">
<span class="kw">class</span> <span class="cl">Welcome</span> <span class="kw">extends</span> <span class="cl">Controller</span> {<br>
&nbsp;&nbsp;<span class="kw">public function</span> <span class="fn">index</span>() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="var">$this</span>-><span class="fn">call</span>-><span class="fn">model</span>(<span class="str">'UserModel'</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="var">$data</span>[<span class="str">'users'</span>] = <span class="var">$this</span>-><span class="cl">UserModel</span>-><span class="fn">all</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="var">$this</span>-><span class="fn">call</span>-><span class="fn">view</span>(<span class="str">'welcome'</span>, <span class="var">$data</span>);<br>
&nbsp;&nbsp;}<br>
}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="divider"></div>

<!-- STRUCTURE -->
<section>
    <div class="wrap">
        <div class="section-label">// project structure</div>
        <h2 class="section-title">Organized by default.</h2>
        <p class="section-desc">A predictable directory layout so every file has a logical home from day one.</p>

        <div class="structure-grid">
            <?php
            // Each entry: [path, SVG path data (viewBox 0 0 24 24, stroke only)]
            $dirs = [
                ['app/config',      '<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
                ['app/controllers', '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>'],
                ['app/helpers',     '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>'],
                ['app/libraries',   '<path d="M4 19V6a2 2 0 0 1 2-2h13a1 1 0 0 1 1 1v13"/><path d="M4 19a2 2 0 0 0 2 2h13a1 1 0 0 0 1-1v-1"/><path d="M8 7h6M8 11h8"/>'],
                ['app/language',    '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
                ['app/middlewares', '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
                ['app/migrations',  '<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>'],
                ['app/models',      '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v4c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 9v5c0 1.66 4 3 9 3s9-1.34 9-3V9"/><path d="M3 14v5c0 1.66 4 3 9 3s9-1.34 9-3v-5"/>'],
                ['app/modules',     '<rect x="2" y="2" width="8" height="8" rx="1.5"/><rect x="14" y="2" width="8" height="8" rx="1.5"/><rect x="2" y="14" width="8" height="8" rx="1.5"/><rect x="14" y="14" width="8" height="8" rx="1.5"/>'],
                ['app/views',       '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>'],
                ['public/',         '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
                ['runtime/',        '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'],
                ['console/',        '<polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/>'],
                ['scheme/',         '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>'],
            ];
            foreach ($dirs as [$name, $svgPaths]): ?>
            <div class="dir-item">
                <span class="dir-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><?php echo $svgPaths; ?></svg>
                </span>
                <?php echo $name; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div class="footer-meta">
            <span>rendered in <span><?php echo lava_instance()->performance->elapsed_time('lavalust'); ?>s</span></span>
            <span>memory <span><?php echo lava_instance()->performance->memory_usage(); ?></span></span>
            <?php if(config_item('environment') === 'development'): ?>
            <span>version <span><?php echo config_item('version'); ?></span></span>
            <span style="color: #dd4814;">● development</span>
            <?php endif; ?>
        </div>
        <div class="footer-links">
            <a href="https://github.com/ronmarasigan/LavaLust" target="_blank">GitHub</a>
            <a href="https://lavalust.netlify.app/docs/" target="_blank">Docs</a>
            <a href="https://opensource.org/licenses/MIT" target="_blank">MIT License</a>
        </div>
    </div>
</footer>
</body>
</html>