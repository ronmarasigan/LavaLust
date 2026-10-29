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

if (!function_exists('dd')) {
    /**
     * Dump and die
     * 
     * @param mixed ...$vars
     */
    function dd(...$vars)
    {
        _dd_render($vars, true);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump
     * 
     * @param mixed ...$vars
     */
    function dump(...$vars)
    {
        _dd_render($vars, false);
    }
}

if (!function_exists('ddt')) {
    /**
     * Dump table
     * 
     * @param array|object $data
     * @param string $label
     */
    function ddt($data, $label = '')
    {
        $data  = (array) $data;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file  = $trace['file'] ?? 'unknown';
        $line  = $trace['line'] ?? '?';

        _dd_assets();
        echo '<div class="dd-wrap dd-table-wrap">';
        echo '<div class="dd-header">';
        echo '<span class="dd-badge dd-badge-table">TABLE</span>';
        if ($label) {
            echo '<span class="dd-label">' . htmlspecialchars($label) . '</span>';
        }
        echo '<span class="dd-location">' . htmlspecialchars(_dd_short_path($file)) . ':' . $line . '</span>';
        echo '</div>';

        if (empty($data)) {
            echo '<div class="dd-empty">Empty array / object</div>';
        } else {
            $first     = reset($data);
            $is_matrix = is_array($first) || is_object($first);

            echo '<div class="dd-table-scroll"><table class="dd-tbl">';

            if ($is_matrix) {
                echo '<thead><tr>';
                foreach (array_keys((array) $first) as $col) {
                    echo '<th>' . htmlspecialchars((string) $col) . '</th>';
                }
                echo '</tr></thead><tbody>';
                foreach ($data as $row) {
                    echo '<tr>';
                    foreach ((array) $row as $cell) {
                        echo '<td>' . htmlspecialchars(_dd_scalar_string($cell)) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            } else {
                echo '<thead><tr><th>key</th><th>value</th><th>type</th></tr></thead><tbody>';
                foreach ($data as $k => $v) {
                    echo '<tr>';
                    echo '<td class="dd-key-color">' . htmlspecialchars((string) $k) . '</td>';
                    echo '<td>' . htmlspecialchars(_dd_scalar_string($v)) . '</td>';
                    echo '<td class="dd-type-color">' . gettype($v) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            }
        }
        echo '</div>';
    }
}

if (!function_exists('ddj')) {
    /**
     * Dump JSON
     * 
     * @param string $data
     */
    function ddj($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data    = ($decoded !== null) ? $decoded : $data;
        }
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file  = $trace['file'] ?? 'unknown';
        $line  = $trace['line'] ?? '?';

        _dd_assets();
        echo '<div class="dd-wrap">';
        echo '<div class="dd-header">';
        echo '<span class="dd-badge dd-badge-json">JSON</span>';
        echo '<span class="dd-location">' . htmlspecialchars(_dd_short_path($file)) . ':' . $line . '</span>';
        echo '</div>';
        echo '<pre class="dd-json">' . htmlspecialchars(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ) . '</pre>';
        echo '</div>';
    }
}

if (!function_exists('_dd_render')) {
    /**
     * Render
     * 
     * @param array $vars
     * @param bool $die
     */
    function _dd_render(array $vars, bool $die)
    {
        $trace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? $trace[0];
        $file   = $caller['file'] ?? 'unknown';
        $line   = $caller['line'] ?? '?';

        _dd_assets();

        echo '<div class="dd-wrap">';
        echo '<div class="dd-header">';
        echo '<span class="dd-badge ' . ($die ? 'dd-badge-die' : 'dd-badge-dump') . '">';
        echo $die ? 'DD' : 'DUMP';
        echo '</span>';
        echo '<span class="dd-location">' . htmlspecialchars(_dd_short_path($file))
            . '<span class="dd-colon">:</span>' . $line . '</span>';
        if ($die) {
            echo '<span class="dd-stopped">execution stopped</span>';
        }
        echo '</div>';

        echo '<div class="dd-body">';
        foreach ($vars as $i => $var) {
            if (count($vars) > 1) {
                echo '<div class="dd-index">#' . ($i + 1) . '</div>';
            }
            echo '<div class="dd-item">';
            echo _dd_format($var, 0);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        if ($die) {
            die();
        }
    }
}

if (!function_exists('_dd_format')) {
    /**
     * Format
     * 
     * @param mixed $var
     * @param int $depth
     * @return string
     */
    function _dd_format($var, int $depth = 0): string
    {
        $type      = gettype($var);
        $max_depth = 6;

        if ($depth > $max_depth) {
            return '<span class="dd-val dd-truncated">...</span>';
        }

        switch ($type) {
            case 'boolean':
                return '<span class="dd-type">bool</span> <span class="dd-val dd-bool">'
                    . ($var ? 'true' : 'false') . '</span>';

            case 'integer':
                return '<span class="dd-type">int</span> <span class="dd-val dd-number">' . $var . '</span>';

            case 'double':
                return '<span class="dd-type">float</span> <span class="dd-val dd-number">' . $var . '</span>';

            case 'string':
                $len  = strlen($var);
                $safe = htmlspecialchars($var);
                return '<span class="dd-type">string</span><span class="dd-length">(' . $len . ')</span> '
                    . '<span class="dd-val dd-string">&quot;' . $safe . '&quot;</span>';

            case 'NULL':
                return '<span class="dd-type">null</span>';

            case 'array':
                $count = count($var);
                if ($count === 0) {
                    return '<span class="dd-type">array</span><span class="dd-length">(0)</span> '
                        . '<span class="dd-val dd-empty-arr">[]</span>';
                }
                $id  = 'dd_' . uniqid();
                $out = '<span class="dd-type dd-collapsible" data-dd-toggle="' . $id . '">'
                    . '<span class="dd-arrow" id="arr_' . $id . '">&#9660;</span>array</span>'
                    . '<span class="dd-length">(' . $count . ')</span>'
                    . '<div class="dd-nested" id="' . $id . '">';
                foreach ($var as $k => $v) {
                    $out .= '<div class="dd-row">'
                        . '<span class="dd-key">' . htmlspecialchars((string) $k) . '</span>'
                        . '<span class="dd-arrow-r">&#10142;</span>'
                        . _dd_format($v, $depth + 1)
                        . '</div>';
                }
                $out .= '</div>';
                return $out;

            case 'object':
                $class = get_class($var);
                $props = (array) $var;
                $count = count($props);
                $id    = 'dd_' . uniqid();
                $out   = '<span class="dd-type dd-obj dd-collapsible" data-dd-toggle="' . $id . '">'
                    . '<span class="dd-arrow" id="arr_' . $id . '">&#9660;</span>object</span> '
                    . '<span class="dd-classname">' . htmlspecialchars($class) . '</span>'
                    . '<span class="dd-length">(' . $count . ')</span>'
                    . '<div class="dd-nested" id="' . $id . '">';
                foreach ($props as $k => $v) {
                    $clean_key  = ltrim($k, "\0*\0" . $class);
                    $visibility = 'public';
                    if (strpos($k, "\0*\0") !== false) {
                        $visibility = 'protected';
                    } elseif (strpos($k, "\0") !== false) {
                        $visibility = 'private';
                    }
                    $out .= '<div class="dd-row">'
                        . '<span class="dd-visibility dd-vis-' . $visibility . '">' . $visibility . '</span> '
                        . '<span class="dd-key">' . htmlspecialchars($clean_key) . '</span>'
                        . '<span class="dd-arrow-r">&#10142;</span>'
                        . _dd_format($v, $depth + 1)
                        . '</div>';
                }
                $out .= '</div>';
                return $out;

            case 'resource':
                return '<span class="dd-type">resource</span> <span class="dd-val dd-resource">'
                    . get_resource_type($var) . '</span>';

            default:
                return '<span class="dd-type">' . $type . '</span> <span class="dd-val">'
                    . htmlspecialchars(print_r($var, true)) . '</span>';
        }
    }
}

if (!function_exists('_dd_scalar_string')) {
    /**
     * Convert scalar to string
     * 
     * @param mixed $val
     */
    function _dd_scalar_string($val): string
    {
        if (is_bool($val))   return $val ? 'true' : 'false';
        if (is_null($val))   return 'null';
        if (is_array($val))  return 'Array';
        if (is_object($val)) return 'Object';
        return (string) $val;
    }
}

if (!function_exists('_dd_short_path')) {
    /**
     * Shorten path
     * 
     * @param string $path
     */
    function _dd_short_path(string $path): string
    {
        $root = defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__) . DIRECTORY_SEPARATOR;
        if ($root && strpos($path, $root) === 0) {
            return ltrim(substr($path, strlen($root)), DIRECTORY_SEPARATOR);
        }
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        return implode(DIRECTORY_SEPARATOR, array_slice($parts, -3));
    }
}

if (!function_exists('_dd_assets')) {
    $_dd_assets_loaded = false;
    /**
     * Assets
     */
    function _dd_assets()
    {
        global $_dd_assets_loaded;
        if ($_dd_assets_loaded) {
            return;
        }
        $_dd_assets_loaded = true;

        // Must match the nonce that Security_headers::apply() put in the CSP header
        $nonce = function_exists('csp_nonce') ? csp_nonce() : '';

        // ---------- CSS ----------
        echo '<style' . ($nonce !== '' ? ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"' : '') . '>';
        echo <<<'CSS'
.dd-wrap{font-family:'JetBrains Mono','Fira Code','Cascadia Code',monospace;font-size:13px;line-height:1.6;margin:1rem 0;border-radius:10px;overflow:hidden;border:1.5px solid #2d2d3a;background:#1a1b26;color:#c0caf5}
.dd-table-wrap{background:#1a1b26}
.dd-header{display:flex;align-items:center;gap:10px;padding:9px 14px;background:#16161e;border-bottom:1px solid #2d2d3a;flex-wrap:wrap}
.dd-badge{font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;letter-spacing:.05em}
.dd-badge-die{background:#f7768e;color:#1a1b26}
.dd-badge-dump{background:#7aa2f7;color:#1a1b26}
.dd-badge-table{background:#9ece6a;color:#1a1b26}
.dd-badge-json{background:#e0af68;color:#1a1b26}
.dd-location{font-size:12px;color:#565f89;margin-left:auto}
.dd-colon{color:#7dcfff}
.dd-stopped{font-size:11px;color:#f7768e;opacity:.8;margin-left:4px}
.dd-label{font-size:12px;color:#bb9af7;font-style:italic}
.dd-body{padding:14px 16px;display:flex;flex-direction:column;gap:10px}
.dd-index{font-size:11px;color:#565f89;margin-bottom:-6px}
.dd-item{padding:8px 10px;background:#16161e;border-radius:6px;border:1px solid #2d2d3a;word-break:break-word}
.dd-type{color:#bb9af7;margin-right:4px;font-style:italic;user-select:none}
.dd-type.dd-obj{color:#7dcfff}
.dd-classname{color:#7dcfff;font-weight:600;margin-right:4px}
.dd-length{color:#565f89;font-size:11px;margin-right:4px}
.dd-val{margin-left:2px}
.dd-string{color:#9ece6a}
.dd-number{color:#ff9e64}
.dd-bool{color:#f7768e;font-weight:600}
.dd-empty-arr{color:#565f89;font-style:italic}
.dd-truncated{color:#565f89;font-style:italic}
.dd-resource{color:#e0af68}
.dd-key{color:#7dcfff;margin-right:6px}
.dd-key-color{color:#7dcfff}
.dd-type-color{color:#bb9af7;font-style:italic}
.dd-visibility{font-size:10px;padding:1px 5px;border-radius:3px;font-weight:600;letter-spacing:.04em;margin-right:4px}
.dd-vis-public{background:#1a3a2a;color:#9ece6a}
.dd-vis-protected{background:#3a2a1a;color:#e0af68}
.dd-vis-private{background:#3a1a1a;color:#f7768e}
.dd-nested{margin-left:18px;border-left:2px solid #2d2d3a;padding-left:10px;margin-top:4px}
.dd-row{margin:3px 0;display:flex;align-items:baseline;flex-wrap:wrap;gap:4px}
.dd-arrow-r{color:#565f89;margin:0 4px;font-size:12px}
.dd-arrow{display:inline-block;font-size:10px;margin-right:5px;transition:transform .15s;cursor:pointer;color:#565f89}
.dd-collapsible{cursor:pointer}
.dd-collapsible:hover{color:#cba6f7}
.dd-empty{padding:10px 16px;color:#565f89;font-style:italic}
.dd-json{margin:0;padding:14px 16px;color:#9ece6a;white-space:pre-wrap;word-break:break-all}
.dd-table-scroll{overflow-x:auto;padding:0 0 2px}
.dd-tbl{width:100%;border-collapse:collapse;font-size:12px}
.dd-tbl th{background:#16161e;color:#7dcfff;padding:7px 12px;text-align:left;border-bottom:1px solid #2d2d3a;font-weight:600;white-space:nowrap}
.dd-tbl td{padding:6px 12px;color:#c0caf5;border-bottom:1px solid #1e1f2e;white-space:nowrap}
.dd-tbl tr:last-child td{border-bottom:none}
.dd-tbl tr:hover td{background:#1e1f2e}
.dd-collapsed{display:none}
.dd-arrow-collapsed{transform:rotate(-90deg)}
CSS;
        echo '</style>';

        // ---------- JS (event delegation – no inline onclick) ----------
        echo '<script' . ($nonce !== '' ? ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"' : '') . '>';
        echo <<<'JS'
(function(){
  function ddToggle(id){
    var el=document.getElementById(id);
    var ar=document.getElementById('arr_'+id);
    if(!el)return;
    var hidden=el.classList.contains('dd-collapsed');
    if(hidden){
      el.classList.remove('dd-collapsed');
      if(ar)ar.classList.remove('dd-arrow-collapsed');
    }else{
      el.classList.add('dd-collapsed');
      if(ar)ar.classList.add('dd-arrow-collapsed');
    }
  }
  document.addEventListener('click',function(e){
    var t=e.target.closest('[data-dd-toggle]');
    if(t){
      e.preventDefault();
      ddToggle(t.getAttribute('data-dd-toggle'));
    }
  });
})();
JS;
        echo '</script>';
    }
}