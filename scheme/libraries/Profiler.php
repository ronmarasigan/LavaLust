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
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * Profiler Class
 */
class Profiler
{
    /** @var array Section visibility flags */
    protected array $sections = [
        'benchmarks'  => true,
        'memory'      => true,
        'queries'     => true,
        'post_data'   => true,
        'get_data'    => true,
        'uri_string'  => true,
        'session'     => true,
        'headers'     => true,
        'config'      => false,
        'php_info'    => false,
    ];

    /** @var array Custom benchmark timers [label => [start, end]] */
    protected array $timers = [];

    /** @var bool Whether output has been sent already */
    protected bool $rendered = false;
    
    /**
     * Constructor registers shutdown function to display profiler.
     */
    public function __construct()
    {
        register_shutdown_function([$this, '_shutdown']);
    }

    /**
     * Enable/disable individual sections.
     *
     * @param array $sections  e.g. ['queries' => false, 'config' => true]
     */
    public function set_sections($sections)
    {
        foreach ($sections as $key => $val) {
            if (array_key_exists($key, $this->sections)) {
                $this->sections[$key] = (bool) $val;
            }
        }
    }

    /**
     * Start a custom benchmark timer.
     *
     * @param string $name  Unique timer name
     */
    public function mark_start($name)
    {
        $this->timers[$name] = ['start' => microtime(true), 'end' => null];
    }

    /**
     * Stop a custom benchmark timer.
     *
     * @param string $name  Timer name passed to mark_start()
     */
    public function mark_end($name)
    {
        if (isset($this->timers[$name])) {
            $this->timers[$name]['end'] = microtime(true);
        }
    }

    /**
     * Render and echo the profiler HTML.
     */
    public function display()
    {
        if ($this->rendered) return;
        $this->rendered = true;
        echo $this->_render();
    }

    /**
     * Shutdown handler to ensure profiler is displayed at the end of the request.
     */
    public function _shutdown()
    {
        $this->display();
    }
    
    /**
     * Internal methods to gather data and render HTML
     */
    protected function _get_benchmarks()
    {
        $rows = [];

        // LavaLust built-in performance class
        if (function_exists('load_class')) {
            try {
                $perf = load_class('performance', 'kernel');
                if (is_object($perf) && method_exists($perf, 'elapsed_time')) {
                    $elapsed = $perf->elapsed_time('lavalust');
                    if ($elapsed !== false && $elapsed !== '') {
                        $rows[] = [
                            'label' => 'LavaLust Execution',
                            'time'  => number_format((float) $elapsed, 4),
                        ];
                    }
                }
            } catch (Throwable $e) {}
        }

        // Custom timers
        foreach ($this->timers as $name => $t) {
            $elapsed = ($t['end'] !== null)
                ? number_format($t['end'] - $t['start'], 4)
                : '(running)';
            $rows[] = ['label' => $name, 'time' => $elapsed];
        }

        // Total page time
        if (defined('LAVALUST_START')) {
            $rows[] = [
                'label' => 'Total Page Time',
                'time'  => number_format(microtime(true) - LAVALUST_START, 4),
            ];
        }

        return $rows;
    }

    /**
     * Attempt to retrieve executed database queries and their execution times.
     * Supports LavaLust's built-in DB class if available.
     * Returns an array of ['query' => ..., 'time' => ...] entries.
     */
    protected function _get_queries()
    {
        $rows = [];

        if (function_exists('lava_instance')) {
            try {
                $lava = lava_instance();

                // Support $this->db directly on the controller instance
                $db = $lava->db ?? null;

                // Also check if it was loaded as a library under any name
                if (is_null($db)) {
                    foreach (['db', 'database', 'Database'] as $prop) {
                        if (isset($lava->$prop) && is_object($lava->$prop) && method_exists($lava->$prop, 'get_query_log')) {
                            $db = $lava->$prop;
                            break;
                        }
                    }
                }

                if ($db && method_exists($db, 'get_query_log')) {
                    foreach ($db->get_query_log() as $entry) {
                        // Interpolate bindings into query for display
                        $display = $entry['query'];
                        foreach ($entry['bindings'] as $binding) {
                            $display = preg_replace('/\?/', "'" . addslashes((string)$binding) . "'", $display, 1);
                        }
                        $rows[] = [
                            'query' => $display,
                            'time'  => number_format($entry['time'], 5),
                        ];
                    }
                }
            } catch (Throwable $e) {}
        }

        return $rows;
    }

    /**
     * Get current and peak memory usage, and memory limit.
     *
     * @return array ['current' => '1.2 MB', 'peak' => '2.5 MB', 'limit' => '128M']
     */
    protected function _get_memory()
    {
        return [
            'current' => $this->_bytes(memory_get_usage(true)),
            'peak'    => $this->_bytes(memory_get_peak_usage(true)),
            'limit'   => ini_get('memory_limit'),
        ];
    }

    /**
     * Retrieve HTTP request headers in a format suitable for display.
     *
     * @return array Associative array of header name => value
     */
    protected function _get_headers()
    {
        $headers = [];
        foreach ($_SERVER as $key => $val) {
            if (strncmp($key, 'HTTP_', 5) === 0) {
                $label = ucwords(str_replace('_', ' ', strtolower(substr($key, 5))));
                $headers[$label] = $val;
            }
        }
        return $headers;
    }

    /**
     * Get session data if session is active.
     *
     * @return array Session data or empty array if no session
     */
    protected function _get_session()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) return [];
        return $_SESSION ?? [];
    }

    /**
     * Attempt to load application config values for display.
     *
     * @return array Config key-value pairs or empty array if not available
     */
    protected function _get_config()
    {
        if (function_exists('load_class')) {
            try {
                $cfg = load_class('config', 'kernel');
                if (is_object($cfg) && property_exists($cfg, 'config')) {
                    return (array) $cfg->config;
                }
            } catch (Throwable $e) {}
        }
        return [];
    }

    /**
     * Get the current request URI for display.
     *
     * @return string Sanitized URI string
     */
    protected function _get_uri()
    {
        return htmlspecialchars($_SERVER['REQUEST_URI'] ?? '');
    }

    /**
     * Render the profiler HTML.
     *
     * @return string HTML content
     */
    protected function _render()
    {
        $id   = 'llprofiler_' . substr(md5(uniqid()), 0, 8);
        $html = $this->_styles($id);

        $html .= '<div id="' . $id . '" class="llp-wrap">';

        // ── Toggle bar ──
        $html .= '<div class="llp-bar" data-llp-toggle="' . $id . '">';
        $html .= '<span class="llp-logo">LavaLust Profiler</span>';
        $html .= '<span class="llp-bar-meta">';

        $mem = $this->_get_memory();
        $html .= '<span class="llp-chip llp-chip-mem">MEM ' . $mem['current'] . '</span>';

        $benchmarks = $this->_get_benchmarks();
        foreach ($benchmarks as $b) {
            if (strpos($b['label'], 'Total') !== false || strpos($b['label'], 'LavaLust') !== false) {
                $html .= '<span class="llp-chip llp-chip-time">⏱ ' . $b['time'] . 's</span>';
                break;
            }
        }

        $queries = $this->_get_queries();
        $html .= '<span class="llp-chip llp-chip-q">' . count($queries) . ' ' . (count($queries) === 1 ? 'query' : 'queries') . '</span>';
        $html .= '<span class="llp-chip llp-chip-method">' . htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'GET') . '</span>';
        $html .= '</span>';
        $html .= '<span class="llp-toggle-icon" id="' . $id . '_icon">▲</span>';
        $html .= '</div>';

        // ── Body ──
        $html .= '<div class="llp-body" id="' . $id . '_body">';

        // Tab nav
        $tabs   = [];
        $panels = [];

        // Benchmarks
        if ($this->sections['benchmarks']) {
            ob_start();
            echo '<table class="llp-table">';
            echo '<thead><tr><th>Label</th><th>Time (sec)</th></tr></thead><tbody>';
            if (empty($benchmarks)) {
                echo '<tr><td colspan="2" class="llp-empty">No benchmarks recorded.</td></tr>';
            }
            foreach ($benchmarks as $b) {
                $cls = ($b['time'] !== '(running)' && (float)$b['time'] > 0.5) ? ' llp-slow' : '';
                echo '<tr class="' . $cls . '"><td>' . htmlspecialchars($b['label']) . '</td><td class="llp-mono">' . $b['time'] . 's</td></tr>';
            }
            echo '</tbody></table>';
            $tabs[]   = ['id' => 'benchmarks', 'label' => 'Benchmarks <span class="llp-cnt">' . count($benchmarks) . '</span>'];
            $panels[] = ['id' => 'benchmarks', 'content' => ob_get_clean()];
        }

        // Memory
        if ($this->sections['memory']) {
            ob_start();
            echo '<div class="llp-mem-grid">';
            foreach ([
                'Current Usage' => $mem['current'],
                'Peak Usage'    => $mem['peak'],
                'Memory Limit'  => $mem['limit'],
            ] as $label => $val) {
                echo '<div class="llp-mem-card"><div class="llp-mem-val">' . $val . '</div><div class="llp-mem-label">' . $label . '</div></div>';
            }
            echo '</div>';
            $tabs[]   = ['id' => 'memory', 'label' => 'Memory'];
            $panels[] = ['id' => 'memory', 'content' => ob_get_clean()];
        }

        // Queries
        if ($this->sections['queries']) {
            ob_start();
            if (empty($queries)) {
                echo '<div class="llp-empty">No queries executed — or DB not loaded.</div>';
            } else {
                echo '<table class="llp-table llp-query-table">';
                echo '<thead><tr><th>#</th><th>Query</th><th>Time</th></tr></thead><tbody>';
                foreach ($queries as $i => $q) {
                    $slow = ($q['time'] !== '-' && (float)$q['time'] > 0.1) ? ' llp-slow' : '';
                    echo '<tr class="' . $slow . '">';
                    echo '<td class="llp-qnum">' . ($i + 1) . '</td>';
                    echo '<td><code class="llp-sql">' . $this->_highlight_sql($q['query']) . '</code></td>';
                    echo '<td class="llp-mono llp-qtime">' . $q['time'] . 's</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
            $tabs[]   = ['id' => 'queries', 'label' => 'Queries <span class="llp-cnt">' . count($queries) . '</span>'];
            $panels[] = ['id' => 'queries', 'content' => ob_get_clean()];
        }

        // POST
        if ($this->sections['post_data']) {
            ob_start();
            $this->_render_kv($_POST, 'No POST data.');
            $tabs[]   = ['id' => 'post', 'label' => 'POST <span class="llp-cnt">' . count($_POST) . '</span>'];
            $panels[] = ['id' => 'post', 'content' => ob_get_clean()];
        }

        // GET
        if ($this->sections['get_data']) {
            ob_start();
            $this->_render_kv($_GET, 'No GET data.');
            $tabs[]   = ['id' => 'get', 'label' => 'GET <span class="llp-cnt">' . count($_GET) . '</span>'];
            $panels[] = ['id' => 'get', 'content' => ob_get_clean()];
        }

        // URI
        if ($this->sections['uri_string']) {
            ob_start();
            echo '<div class="llp-uri">' . $this->_get_uri() . '</div>';
            $tabs[]   = ['id' => 'uri', 'label' => 'URI'];
            $panels[] = ['id' => 'uri', 'content' => ob_get_clean()];
        }

        // Session
        if ($this->sections['session']) {
            ob_start();
            $this->_render_kv($this->_get_session(), 'No session data.');
            $tabs[]   = ['id' => 'session', 'label' => 'Session <span class="llp-cnt">' . count($this->_get_session()) . '</span>'];
            $panels[] = ['id' => 'session', 'content' => ob_get_clean()];
        }

        // Headers
        if ($this->sections['headers']) {
            ob_start();
            $this->_render_kv($this->_get_headers(), 'No HTTP headers found.');
            $tabs[]   = ['id' => 'headers', 'label' => 'Headers'];
            $panels[] = ['id' => 'headers', 'content' => ob_get_clean()];
        }

        // Config
        if ($this->sections['config']) {
            ob_start();
            $cfg = $this->_get_config();
            $this->_render_kv($cfg, 'Config not available.');
            $tabs[]   = ['id' => 'config', 'label' => 'Config <span class="llp-cnt">' . count($cfg) . '</span>'];
            $panels[] = ['id' => 'config', 'content' => ob_get_clean()];
        }

        // PHP Info
        if ($this->sections['php_info']) {
            ob_start();
            echo '<table class="llp-table"><tbody>';
            $phpinfo = [
                'PHP Version'       => PHP_VERSION,
                'OS'                => PHP_OS,
                'SAPI'              => php_sapi_name(),
                'Max Execution'     => ini_get('max_execution_time') . 's',
                'Upload Max'        => ini_get('upload_max_filesize'),
                'Post Max'          => ini_get('post_max_size'),
                'Extensions'        => implode(', ', get_loaded_extensions()),
            ];
            foreach ($phpinfo as $k => $v) {
                echo '<tr><td class="llp-kv-key">' . $k . '</td><td class="llp-kv-val">' . htmlspecialchars($v) . '</td></tr>';
            }
            echo '</tbody></table>';
            $tabs[]   = ['id' => 'phpinfo', 'label' => 'PHP Info'];
            $panels[] = ['id' => 'phpinfo', 'content' => ob_get_clean()];
        }

        // Render tab bar
        $html .= '<div class="llp-tabs" id="' . $id . '_tabs">';
        foreach ($tabs as $i => $tab) {
            $active = $i === 0 ? ' llp-tab-active' : '';
            $html .= '<button type="button" class="llp-tab' . $active . '" data-llp-wrap="' . $id . '" data-llp-tab="' . $tab['id'] . '">' . $tab['label'] . '</button>';
        }
        $html .= '</div>';

        // Render panels
        $html .= '<div class="llp-panels">';
        foreach ($panels as $i => $panel) {
            $hidden = $i === 0 ? '' : ' llp-hide';
            $html .= '<div class="llp-panel' . $hidden . '" id="' . $id . '_panel_' . $panel['id'] . '">';
            $html .= $panel['content'];
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '</div>'; // llp-body
        $html .= '</div>'; // llp-wrap

        return $html;
    }

    /**
     * Helper to render key-value data in a table format, with handling for empty datasets.
     *
     * @param array $data  Associative array of key-value pairs
     * @param string $empty_msg Message to display if $data is empty
     */
    protected function _render_kv($data, $empty_msg)
    {
        if (empty($data)) {
            echo '<div class="llp-empty">' . $empty_msg . '</div>';
            return;
        }
        echo '<table class="llp-table"><tbody>';
        foreach ($data as $k => $v) {
            echo '<tr>';
            echo '<td class="llp-kv-key">' . htmlspecialchars((string) $k) . '</td>';
            echo '<td class="llp-kv-val">';
            if (is_array($v) || is_object($v)) {
                echo '<pre class="llp-pre">' . htmlspecialchars(print_r($v, true)) . '</pre>';
            } else {
                echo htmlspecialchars((string) $v);
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Simple SQL syntax highlighter for better readability in the profiler.
     *
     * @param string $sql
     * @return string HTML with basic syntax highlighting
     */
    protected function _highlight_sql($sql)
    {
        $keywords = ['SELECT','FROM','WHERE','JOIN','LEFT','RIGHT','INNER','OUTER',
                     'ON','AS','AND','OR','NOT','IN','IS','NULL','ORDER','BY','GROUP',
                     'HAVING','LIMIT','OFFSET','INSERT','INTO','VALUES','UPDATE','SET',
                     'DELETE','CREATE','TABLE','DROP','ALTER','INDEX','DISTINCT','COUNT',
                     'SUM','AVG','MAX','MIN','UNION','ALL'];
        $sql = htmlspecialchars($sql);
        foreach ($keywords as $kw) {
            $sql = preg_replace(
                '/\b' . $kw . '\b/i',
                '<span class="llp-sql-kw">' . $kw . '</span>',
                $sql
            );
        }
        return $sql;
    }

    /**
     * Convert a byte count into a human-readable format (B, KB, MB).
     *
     * @param int $bytes
     * @return string
     */
    protected function _bytes($bytes)
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Generate CSS styles for the profiler. Uses a static variable to ensure styles are only included once.
     *
     * @param string $id Unique ID for scoping styles if needed
     * @return string CSS styles wrapped in a <style> tag
     */
    protected function _styles($id)
    {
        static $injected = false;
        $css = '';
        $nonce = defined('CSP_NONCE') ? CSP_NONCE : '';
        if (!$injected) {
            $injected = true;
            $css = <<<CSS
<style nonce="{$nonce}">
:root{--llp-bg:#0d0e14;--llp-bg2:#13141d;--llp-bg3:#1a1b26;--llp-border:#2a2b3d;--llp-text:#c0caf5;--llp-muted:#565f89;--llp-blue:#7aa2f7;--llp-green:#9ece6a;--llp-yellow:#e0af68;--llp-red:#f7768e;--llp-purple:#bb9af7;--llp-teal:#7dcfff;--llp-bar:#0a0b10}
.llp-wrap{position:fixed;bottom:0;left:0;right:0;z-index:99999;font-family:'JetBrains Mono','Fira Code','Cascadia Code',monospace;font-size:12px;color:var(--llp-text);box-shadow:0 -4px 24px rgba(0,0,0,.5)}
.llp-bar{display:flex;align-items:center;gap:10px;padding:8px 16px;background:var(--llp-bar);border-top:1px solid var(--llp-border);cursor:pointer;user-select:none;transition:background .15s}
.llp-bar:hover{background:#111218}
.llp-logo{font-weight:700;color:var(--llp-blue);letter-spacing:.03em;font-size:12px}
.llp-bar-meta{display:flex;align-items:center;gap:6px;margin-left:12px;flex-wrap:wrap}
.llp-chip{font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;letter-spacing:.04em}
.llp-chip-time{background:#1a2a3a;color:var(--llp-blue)}
.llp-chip-mem{background:#1a2a1a;color:var(--llp-green)}
.llp-chip-q{background:#2a1a2a;color:var(--llp-purple)}
.llp-chip-method{background:#2a2a1a;color:var(--llp-yellow)}
.llp-toggle-icon{margin-left:auto;color:var(--llp-muted);font-size:10px;transition:transform .2s}
.llp-body{background:var(--llp-bg);border-top:1px solid var(--llp-border);max-height:480px;display:flex;flex-direction:column}
.llp-tabs{display:flex;flex-wrap:wrap;gap:0;background:var(--llp-bg2);border-bottom:1px solid var(--llp-border);}
.llp-tab{background:none;border:none;border-bottom:2px solid transparent;color:var(--llp-muted);padding:7px 13px;cursor:pointer;font-family:inherit;font-size:11px;white-space:nowrap;transition:color .15s,border-color .15s;flex-shrink:0}
.llp-tab:hover{color:var(--llp-text)}
.llp-tab-active{color:var(--llp-blue);border-bottom-color:var(--llp-blue)}
.llp-panels{overflow-y:auto;flex:1;padding:12px 16px}
.llp-panel{}
.llp-hide{display:none!important}
.llp-table{width:100%;border-collapse:collapse;font-size:12px}
.llp-table th{color:var(--llp-muted);text-transform:uppercase;font-size:10px;letter-spacing:.08em;padding:6px 10px;border-bottom:1px solid var(--llp-border);text-align:left;font-weight:600}
.llp-table td{padding:6px 10px;border-bottom:1px solid #1a1b26;vertical-align:top}
.llp-table tr:hover td{background:#13141d}
.llp-table tr.llp-slow td{background:#2a1a1a}
.llp-table tr.llp-slow td:first-child{border-left:2px solid var(--llp-red)}
.llp-mono{color:var(--llp-yellow);font-variant-numeric:tabular-nums;white-space:nowrap}
.llp-kv-key{color:var(--llp-teal);width:200px;white-space:nowrap;font-weight:500}
.llp-kv-val{color:var(--llp-text)}
.llp-empty{color:var(--llp-muted);font-style:italic;padding:12px 10px}
.llp-cnt{background:var(--llp-border);color:var(--llp-muted);padding:1px 5px;border-radius:8px;font-size:10px;margin-left:3px}
.llp-uri{color:var(--llp-green);padding:10px;background:var(--llp-bg2);border-radius:6px;word-break:break-all}
.llp-sql{background:none;font-family:inherit;font-size:12px;color:var(--llp-text);white-space:pre-wrap;word-break:break-word;display:block}
.llp-sql-kw{color:var(--llp-blue);font-weight:600}
.llp-qnum{color:var(--llp-muted);width:28px;text-align:center}
.llp-qtime{width:70px;text-align:right}
.llp-pre{margin:0;white-space:pre-wrap;color:var(--llp-text);font-size:11px}
.llp-mem-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:4px 0}
.llp-mem-card{background:var(--llp-bg2);border:1px solid var(--llp-border);border-radius:8px;padding:14px 16px;text-align:center}
.llp-mem-val{font-size:20px;font-weight:700;color:var(--llp-green);margin-bottom:4px}
.llp-mem-label{font-size:11px;color:var(--llp-muted)}
.llp-query-table td{font-size:12px}
</style>
CSS;
        

        $css .= <<<JS
<script nonce="{$nonce}">
document.addEventListener('click', function (e) {
  var tab = e.target.closest('[data-llp-tab]');
  if (tab) {
    var wrap = document.getElementById(tab.getAttribute('data-llp-wrap'));
    if (!wrap) return;
    var wid = wrap.id;
    wrap.querySelectorAll('.llp-panel').forEach(function (p) { p.classList.add('llp-hide'); });
    wrap.querySelectorAll('.llp-tab').forEach(function (t) { t.classList.remove('llp-tab-active'); });
    var panel = document.getElementById(wid + '_panel_' + tab.getAttribute('data-llp-tab'));
    if (panel) panel.classList.remove('llp-hide');
    tab.classList.add('llp-tab-active');
    return;
  }

  var bar = e.target.closest('[data-llp-toggle]');
  if (bar) {
    var id = bar.getAttribute('data-llp-toggle');
    var body = document.getElementById(id + '_body');
    var icon = document.getElementById(id + '_icon');
    if (!body) return;
    var nowHidden = body.classList.toggle('llp-hide');
    if (icon) icon.textContent = nowHidden ? '▼' : '▲';
  }
});
</script>
JS;
        return $css;
    }
    }
}