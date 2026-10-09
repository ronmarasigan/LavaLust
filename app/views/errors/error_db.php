<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

while (ob_get_level() > 0) {
    ob_end_clean();
}

function get_code_excerpt($file, $errorLine, $padding = 10) {
    if (!is_readable($file)) return [[], 0];
    $lines = file($file);
    $start = max($errorLine - $padding - 1, 0);
    $end   = min($errorLine + $padding - 1, count($lines) - 1);
    $excerpt = array_slice($lines, $start, $end - $start + 1, true);
    return [$excerpt, $start + 1];
}

$ex      = isset($exception) && $exception instanceof Throwable ? $exception : null;
$filePath = $ex ? $ex->getFile() : 'Unknown';
$lineNum  = $ex ? $ex->getLine() : 0;
$errMsg   = $ex ? $ex->getMessage() : ($exception_message ?? $message ?? 'Unknown database error');

list($codeExcerpt) = get_code_excerpt($filePath, $lineNum);

// Build frames for JS
$traceFrames = $ex ? $ex->getTrace() : [];
$allFrames   = [];
$allFrames[] = [
    'file'     => $filePath,
    'line'     => $lineNum,
    'function' => '{main}',
    'class'    => '',
    'type'     => '',
    'args'     => [],
];
foreach ($traceFrames as $frame) {
    if (!empty($frame['file'])) $allFrames[] = $frame;
}

$plainTrace = (!empty($trace) && is_string($trace)) ? $trace : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Error &mdash; LavaLust</title>
    <style nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-deep:    #0d0d18;
            --bg-panel:   #11111e;
            --bg-surface: #16162a;
            --bg-hover:   #1c1c2e;
            --bg-active:  #1e1e36;
            --border:     #1f1f32;
            --border-mid: #2a2a42;
            --text:       #c8c8e0;
            --text-muted: #55556a;
            --text-dim:   #44445a;
            --accent:     #2dd4bf;
            --accent-dim: rgba(45,212,191,0.13);
            --blue:       #3b82f6;
            --purple:     #a78bfa;
            --green:      #86efac;
            --orange:     #fb923c;
            --pink:       #f472b6;
            --cyan:       #67e8f9;
        }

        html, body { height: 100%; overflow: hidden; }

        body {
            background: var(--bg-deep);
            color: var(--text);
            font-family: 'SF Mono','Fira Code','Cascadia Code','JetBrains Mono',monospace;
            font-size: 13px;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP BAR ── */
        .top-bar {
            background: #0e4a45;
            border-bottom: 2px solid var(--accent);
            color: #fff;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            min-height: 46px;
        }
        .top-bar .exc-type {
            background: var(--accent);
            color: #031a18;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            white-space: nowrap;
        }
        .top-bar .exc-msg {
            font-family: system-ui,-apple-system,sans-serif;
            font-size: 13px;
            font-weight: 500;
            flex: 1;
            line-height: 1.4;
            color: #b2f5ea;
        }

        /* ── LAYOUT ── */
        .layout { display: flex; flex: 1; overflow: hidden; min-height: 0; }

        /* ── LEFT SIDEBAR ── */
        .sidebar {
            width: 360px;
            min-width: 260px;
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-shrink: 0;
        }
        .sidebar-title {
            font-size: 10px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--text-dim);
            padding: 10px 16px 9px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .trace-list { flex: 1; overflow-y: auto; overflow-x: hidden; }
        .trace-list::-webkit-scrollbar { width: 4px; }
        .trace-list::-webkit-scrollbar-track { background: transparent; }
        .trace-list::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 2px; }

        .trace-item {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: background .1s;
        }
        .trace-item:hover { background: var(--bg-hover); }
        .trace-item.active { background: var(--bg-active); border-left-color: var(--accent); }

        .trace-fn {
            color: var(--purple);
            font-size: 12px;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .trace-item.active .trace-fn { color: #c4b5fd; }
        .trace-loc {
            color: var(--text-muted);
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .trace-loc .line-num { color: var(--blue); }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
            background: var(--bg-deep);
        }

        /* SQL / bindings strip above code */
        .sql-strip {
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .sql-tabs-bar { display: flex; border-bottom: 1px solid var(--border); }
        .sql-tab {
            padding: 7px 14px;
            font-size: 10.5px;
            letter-spacing: .5px;
            text-transform: uppercase;
            cursor: pointer;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            background: none;
            border-top: none; border-left: none; border-right: none;
            font-family: inherit;
            transition: color .15s;
        }
        .sql-tab:hover { color: var(--text); }
        .sql-tab.active { color: var(--accent); border-bottom-color: var(--accent); }
        .sql-pane { display: none; max-height: 110px; overflow: auto; padding: 10px 16px; font-size: 12px; color: #9ab4ff; white-space: pre-wrap; word-break: break-all; }
        .sql-pane.active { display: block; }
        .sql-pane::-webkit-scrollbar { height: 4px; width: 4px; }
        .sql-pane::-webkit-scrollbar-thumb { background: var(--border-mid); }
        .no-data { color: var(--text-dim); font-size: 11.5px; }

        /* SQL keywords */
        .sk { color: var(--accent); font-weight: 600; }

        /* code view */
        .code-header {
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border);
            padding: 9px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .code-header .file-path {
            color: #7dd3fc;
            font-size: 12px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .code-header .line-badge {
            background: var(--accent-dim);
            color: var(--accent);
            font-size: 11px;
            padding: 2px 9px;
            border-radius: 4px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .code-view { flex: 1; overflow: auto; font-size: 12.5px; line-height: 1.75; }
        .code-view::-webkit-scrollbar { width: 6px; height: 6px; }
        .code-view::-webkit-scrollbar-track { background: transparent; }
        .code-view::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 3px; }

        .code-line { display: flex; align-items: stretch; min-width: max-content; }
        .code-line:hover { background: rgba(255,255,255,.025); }
        .code-line.error-line { background: rgba(45,212,191,.10); }

        .ln {
            width: 52px;
            text-align: right;
            padding: 0 16px 0 0;
            color: var(--text-dim);
            user-select: none;
            flex-shrink: 0;
            font-size: 12px;
        }
        .code-line.error-line .ln { color: var(--accent); font-weight: 700; }

        .error-arrow { width: 16px; color: var(--accent); flex-shrink: 0; font-size: 12px; }
        .src { flex: 1; padding-right: 32px; white-space: pre; color: var(--text); }
        .code-line.error-line .src { color: #fff; }

        .kw  { color: var(--pink); }
        .str { color: var(--green); }
        .num { color: var(--orange); }
        .cmt { color: var(--text-dim); font-style: italic; }
        .fn  { color: var(--blue); }
        .var { color: var(--cyan); }

        /* args panel */
        .args-panel { border-top: 1px solid var(--border); background: var(--bg-panel); flex-shrink: 0; max-height: 120px; overflow: auto; }
        .args-panel::-webkit-scrollbar { width: 4px; }
        .args-panel::-webkit-scrollbar-thumb { background: var(--border-mid); }
        .args-title { font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: var(--text-dim); padding: 8px 18px 6px; border-bottom: 1px solid var(--border); }
        .args-body { padding: 10px 18px; font-size: 11.5px; color: #888; white-space: pre-wrap; word-break: break-all; }

        /* info tabs */
        .info-section { border-top: 1px solid var(--border); background: var(--bg-panel); flex-shrink: 0; }
        .tabs-bar { display: flex; border-bottom: 1px solid var(--border); }
        .tab-btn {
            padding: 8px 16px;
            font-size: 10.5px;
            letter-spacing: .6px;
            text-transform: uppercase;
            cursor: pointer;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            background: none;
            border-top: none; border-left: none; border-right: none;
            font-family: inherit;
            transition: color .15s;
        }
        .tab-btn:hover { color: var(--text); }
        .tab-btn.active { color: var(--purple); border-bottom-color: var(--purple); }

        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .info-table-wrap { max-height: 120px; overflow: auto; padding: 6px 0; }
        .info-table-wrap::-webkit-scrollbar { width: 4px; }
        .info-table-wrap::-webkit-scrollbar-thumb { background: var(--border-mid); }
        .info-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .info-table td { padding: 3px 18px; vertical-align: top; }
        .info-table td:first-child { color: var(--text-dim); width: 160px; white-space: nowrap; }
        .info-table td:last-child { color: #9ab4ff; word-break: break-all; }
        .empty-note { padding: 10px 18px; color: var(--text-dim); font-size: 11.5px; }

        @media (max-width: 700px) { .sidebar { width: 220px; min-width: 160px; } }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <span class="exc-type">Database Error</span>
    <span class="exc-msg"><?= htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8') ?></span>
</div>

<div class="layout">

    <!-- LEFT SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-title">Stack frames</div>
        <div class="trace-list" id="traceList">
            <?php foreach ($allFrames as $i => $frame): ?>
            <div class="trace-item <?= $i === 0 ? 'active' : '' ?>"
                 data-index="<?= $i ?>">
                <div class="trace-fn">
                    <?php if (!empty($frame['class'])): ?>
                        <?= htmlspecialchars($frame['class'] . ($frame['type'] ?? '->')) ?>
                    <?php endif; ?>
                    <?= htmlspecialchars($frame['function'] ?? '{closure}') ?>()
                </div>
                <div class="trace-loc">
                    <?php
                    $shortFile = isset($frame['file'])
                        ? implode('/', array_slice(explode('/', str_replace('\\', '/', $frame['file'])), -3))
                        : '[internal]';
                    ?>
                    <?= htmlspecialchars($shortFile) ?>
                    <?php if (!empty($frame['line'])): ?>
                        <span class="line-num">:<?= $frame['line'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($allFrames) && !empty($plainTrace)): ?>
            <div style="padding:12px 14px; color:var(--text-muted); font-size:11px; white-space:pre-wrap;">
                <?= htmlspecialchars($plainTrace) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">

        <!-- SQL / BINDINGS STRIP -->
        <div class="sql-strip">
            <div class="sql-tabs-bar">
                <button class="sql-tab active" data-sql-tab="sql">SQL Query</button>
                <button class="sql-tab" data-sql-tab="bindings">Bindings</button>
            </div>
            <div class="sql-pane active" id="spane-sql">
                <?php if (!empty($query)): ?>
                    <span id="sqlContent"><?= htmlspecialchars($query) ?></span>
                <?php else: ?>
                    <span class="no-data">No query available.</span>
                <?php endif; ?>
            </div>
            <div class="sql-pane" id="spane-bindings">
                <?php if (!empty($bindings_data)): ?>
                    <?= htmlspecialchars($bindings_data) ?>
                <?php else: ?>
                    <span class="no-data">No bindings.</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- CODE HEADER -->
        <div class="code-header">
            <span class="file-path" id="codeFilePath"><?= htmlspecialchars($filePath) ?></span>
            <span class="line-badge" id="codeLineBadge">line <?= $lineNum ?></span>
        </div>

        <!-- CODE VIEW -->
        <div class="code-view" id="codeView">
            <?php foreach ($codeExcerpt as $lineIdx => $codeLine):
                $currentLine = $lineIdx + 1;
                $isError = ($currentLine === $lineNum);
            ?>
            <div class="code-line <?= $isError ? 'error-line' : '' ?>">
                <span class="ln"><?= $currentLine ?></span>
                <span class="error-arrow"><?= $isError ? '&#9654;' : '&nbsp;' ?></span>
                <span class="src" data-raw="<?= htmlspecialchars(rtrim($codeLine), ENT_QUOTES) ?>"></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ARGS PANEL -->
        <div class="args-panel" id="argsPanel" style="display:none">
            <div class="args-title">Arguments</div>
            <div class="args-body" id="argsBody"></div>
        </div>

        <!-- INFO TABS -->
        <div class="info-section">
            <div class="tabs-bar">
                <button class="tab-btn active" data-tab="env">Environment</button>
                <button class="tab-btn" data-tab="get">GET</button>
                <button class="tab-btn" data-tab="post">POST</button>
                <button class="tab-btn" data-tab="server">Server</button>
            </div>

            <div class="tab-pane active" id="pane-env">
                <div class="info-table-wrap"><table class="info-table">
                    <tr><td>PHP Version</td><td><?= phpversion() ?></td></tr>
                    <tr><td>LavaLust</td><td><?= htmlspecialchars(config_item('version') ?? 'Unknown') ?></td></tr>
                    <tr><td>Environment</td><td><?= htmlspecialchars(config_item('environment')) ?></td></tr>
                    <tr><td>Error type</td><td>Database</td></tr>
                    <tr><td>Memory Used</td><td><?= round(memory_get_peak_usage(true) / 1048576, 2) ?> MB</td></tr>
                </table></div>
            </div>

            <div class="tab-pane" id="pane-get">
                <?php if (!empty($_GET)): ?>
                <div class="info-table-wrap"><table class="info-table">
                    <?php foreach ($_GET as $k => $v): ?>
                    <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars(is_array($v) ? json_encode($v) : $v) ?></td></tr>
                    <?php endforeach; ?>
                </table></div>
                <?php else: ?><div class="empty-note">No GET data.</div><?php endif; ?>
            </div>

            <div class="tab-pane" id="pane-post">
                <?php if (!empty($_POST)): ?>
                <div class="info-table-wrap"><table class="info-table">
                    <?php foreach ($_POST as $k => $v): ?>
                    <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars(is_array($v) ? json_encode($v) : $v) ?></td></tr>
                    <?php endforeach; ?>
                </table></div>
                <?php else: ?><div class="empty-note">No POST data.</div><?php endif; ?>
            </div>

            <div class="tab-pane" id="pane-server">
                <div class="info-table-wrap"><table class="info-table">
                    <?php foreach (['REQUEST_METHOD','REQUEST_URI','HTTP_HOST','REMOTE_ADDR',
                                     'SERVER_SOFTWARE','SERVER_PORT','HTTPS','HTTP_USER_AGENT'] as $key):
                        if (isset($_SERVER[$key])): ?>
                    <tr><td><?= htmlspecialchars($key) ?></td><td><?= htmlspecialchars($_SERVER[$key]) ?></td></tr>
                    <?php endif; endforeach; ?>
                </table></div>
            </div>
        </div>
    </div>
</div>

<!-- Frame data + SQL highlight -->
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
const FRAMES = <?php
$jsFrames = [];
foreach ($allFrames as $frame) {
    $f  = $frame['file'] ?? null;
    $ln = $frame['line'] ?? null;
    $code = [];
    if ($f && $ln) {
        list($excerpt) = get_code_excerpt($f, $ln, 10);
        foreach ($excerpt as $idx => $src) {
            $code[] = ['n' => $idx + 1, 's' => rtrim($src)];
        }
    }
    $args = !empty($frame['args']) ? print_r($frame['args'], true) : '';
    $jsFrames[] = ['file' => $f ?? '', 'line' => $ln ?? 0, 'code' => $code, 'args' => $args];
}
echo json_encode($jsFrames, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>;

/* ─────────── PHP highlighter (unchanged) ─────────── */
function hl(src) {
    src = src.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const frozen = [];
    let out = '', i = 0;
    while (i < src.length) {
        if (src[i]==='/' && src[i+1]==='/') {
            let e = src.indexOf('\n',i); if(e===-1) e=src.length;
            frozen.push('<span class="cmt">'+src.slice(i,e)+'</span>');
            out+='@@FROZEN_'+(frozen.length-1)+'@@'; i=e; continue;
        }
        if (src[i]==='#') {
            let e = src.indexOf('\n',i); if(e===-1) e=src.length;
            frozen.push('<span class="cmt">'+src.slice(i,e)+'</span>');
            out+='@@FROZEN_'+(frozen.length-1)+'@@'; i=e; continue;
        }
        if (src[i]==="'") {
            let j=i+1;
            while(j<src.length){if(src[j]==='\\'){j+=2;continue;}if(src[j]==="'"){j++;break;}j++;}
            frozen.push('<span class="str">'+src.slice(i,j)+'</span>');
            out+='@@FROZEN_'+(frozen.length-1)+'@@'; i=j; continue;
        }
        if (src[i]==='"') {
            let j=i+1;
            while(j<src.length){if(src[j]==='\\'){j+=2;continue;}if(src[j]==='"'){j++;break;}j++;}
            frozen.push('<span class="str">'+src.slice(i,j)+'</span>');
            out+='@@FROZEN_'+(frozen.length-1)+'@@'; i=j; continue;
        }
        out+=src[i++];
    }
    src=out;
    src=src.replace(/\b(function|class|public|protected|private|static|return|new|if|else|elseif|foreach|for|while|switch|case|default|break|continue|throw|try|catch|finally|require|require_once|include|include_once|namespace|use|extends|implements|abstract|interface|trait|echo|print|list|array|null|true|false|void|OR|AND)\b/g,'<span class="kw">$1</span>');
    src=src.replace(/(\$[a-zA-Z_][a-zA-Z0-9_]*)/g,'<span class="var">$1</span>');
    src=src.replace(/\b([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/g,function(m,name){
        if(/^(if|else|elseif|for|foreach|while|switch|catch|list|array|function)$/.test(name))return m;
        return '<span class="fn">'+name+'</span>(';
    });
    src=src.replace(/\b(\d+\.?\d*)\b/g,'<span class="num">$1</span>');
    src=src.replace(/@@FROZEN_(\d+)@@/g,(_,n)=>frozen[+n]);
    return src;
}

/* ─────────── SQL highlighter (unchanged) ─────────── */
function hlSql(src) {
    src = src.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const SQL_KW = /\b(SELECT|INSERT|UPDATE|DELETE|FROM|WHERE|JOIN|LEFT|RIGHT|INNER|OUTER|ON|AS|AND|OR|NOT|IN|IS|NULL|LIKE|BETWEEN|ORDER|BY|GROUP|HAVING|LIMIT|OFFSET|SET|VALUES|INTO|CREATE|DROP|ALTER|TABLE|INDEX|DISTINCT|COUNT|SUM|AVG|MIN|MAX|CASE|WHEN|THEN|ELSE|END|UNION|ALL|EXISTS|RETURNING)\b/gi;
    src = src.replace(SQL_KW, '<span class="sk">$1</span>');
    src = src.replace(/('(?:[^'\\]|\\.)*')/g, '<span class="str">$1</span>');
    src = src.replace(/\b(\d+\.?\d*)\b/g, '<span class="num">$1</span>');
    return src;
}

/* ─────────── Render a frame ─────────── */
function renderCode(frameIdx) {
    const f = FRAMES[frameIdx];
    if (!f) return;

    document.getElementById('codeFilePath').textContent = f.file || '[internal]';
    document.getElementById('codeLineBadge').textContent = f.line ? 'line ' + f.line : '';

    const view = document.getElementById('codeView');
    view.innerHTML = f.code.map(row => {
        const isErr = row.n === f.line;
        return '<div class="code-line'+(isErr?' error-line':'')+'">'+
            '<span class="ln">'+row.n+'</span>'+
            '<span class="error-arrow">'+(isErr?'&#9654;':'&nbsp;')+'</span>'+
            '<span class="src">'+hl(row.s)+'</span>'+
            '</div>';
    }).join('');

    const errLine = view.querySelector('.error-line');
    if (errLine) errLine.scrollIntoView({block:'center'});

    const argsPanel = document.getElementById('argsPanel');
    if (f.args) {
        document.getElementById('argsBody').textContent = f.args;
        argsPanel.style.display = 'block';
    } else {
        argsPanel.style.display = 'none';
    }
}

function activateFrame(idx) {
    document.querySelectorAll('.trace-item').forEach((el,i) =>
        el.classList.toggle('active', i === idx)
    );
    renderCode(idx);
}

/* ─────────── Delegated click handlers (no inline JS) ─────────── */
document.addEventListener('click', function (e) {
    // Trace frame items
    const trace = e.target.closest('.trace-item');
    if (trace) {
        activateFrame(parseInt(trace.dataset.index, 10));
        return;
    }
    // SQL sub-tabs
    const sqlTab = e.target.closest('.sql-tab');
    if (sqlTab) {
        document.querySelectorAll('.sql-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.sql-pane').forEach(p => p.classList.remove('active'));
        sqlTab.classList.add('active');
        const pane = document.getElementById('spane-' + sqlTab.dataset.sqlTab);
        if (pane) pane.classList.add('active');
        return;
    }
    // Info tabs
    const tab = e.target.closest('.tab-btn');
    if (tab) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        const pane = document.getElementById('pane-' + tab.dataset.tab);
        if (pane) pane.classList.add('active');
    }
});

/* ─────────── Initialise ─────────── */
// Highlight SQL
const sqlEl = document.getElementById('sqlContent');
if (sqlEl) sqlEl.innerHTML = hlSql(sqlEl.textContent);

// Render the first frame immediately (the current one).
if (FRAMES.length) {
    activateFrame(0);
} else {
    // No frames — fall back to the PHP-rendered excerpt if present.
    document.querySelectorAll('.src[data-raw]').forEach(el => {
        el.innerHTML = hl(el.getAttribute('data-raw'));
        el.removeAttribute('data-raw');
    });
    const initErr = document.querySelector('.code-line.error-line');
    if (initErr) initErr.scrollIntoView({block:'center'});
}
</script>
</body>
</html>