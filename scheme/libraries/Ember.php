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
* ------------------------------------------------------
*  Class Ember
* ------------------------------------------------------
 */
class Ember
{
    /**
     * Cache file permissions
     *
     * @var int
     */
    public const CACHE_PERMISSIONS = 0644;

    /**
     * Templates path
     *
     * @var string
     */
    public string $templates_path;

    /**
     * Cache path
     *
     * @var string
     */
    public string $cache_path;

    /** Registered globals
     *
     * @var array
     */
    public array $globals = [];

    /** Registered functions
     *
     * @var array
     */
    public array $functions = [];

    /** Registered filters
     *
     * @var array
     */
    public array $filters = [];

    /** Auto-escape output
     *
     * @var bool
     */
    public bool $auto_escape;

    /** Enable raw PHP blocks in templates
     *
     * @var bool
     */
    protected bool $enable_php_blocks = false;

    /** Default escape context (e.g. 'html', 'js', 'attr')
     *
     * @var string
     */
    protected string $escape_context = 'html';
    
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct()
    {
        $_lava = lava_instance();

        $_lava->config->load('ember');

        if(! config_item('ember_helper_enabled')) {
            show_error('Ember Helper is disabled or set up incorrectly.');
        }
        
        $this->templates_path = rtrim(config_item('templates_path'), '/\\') . DIRECTORY_SEPARATOR;
        $this->cache_path     = rtrim(config_item('cache_path'), '/\\') . DIRECTORY_SEPARATOR;

        $this->auto_escape      = (bool) config_item('auto_escape');
        $this->enable_php_blocks = (bool) config_item('enable_php_blocks');
        $this->escape_context   = config_item('escape_context');
        
        if (!is_dir($this->cache_path)) {
            if (!mkdir($this->cache_path, 0755, true)) {
                show_error('Unable to create template cache directory.');
            }
        }
        load_class('Security_headers', 'libraries/Ember');
        // Register built-in filters and functions
        $registrar = load_class('Ember_registrar', 'libraries/Ember');
        $registrar->register($this);
        
    }

    /**
     * Escape output if auto_escape is enabled
     *
     * @param string $value
     * @return string
     */
    public function escape($value, $context = null)
    {
        if ($value === null) {
            return '';
        }

        $context = $context ?? $this->escape_context;

        if (!$this->auto_escape) {
            return (string) $value;
        }

        $value = (string) $value;

        switch ($context) {
            case 'attr':
                return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            case 'js':
                return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            case 'url':
                return rawurlencode($value);
            case 'html':
            default:
                return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    /**
     * Add a global variable
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function add_global($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * Add a function
     *
     * @param string $name
     * @param callable $callable
     * @return void
     */
    public function add_function($name, $callable)
    {
        $this->functions[$name] = $callable;
    }

    /**
     * Apply a filter to a value
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function add_filter($name, $callable)
    {
        $this->filters[$name] = $callable;
    }

    /**
     * Apply a filter to a value
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function apply_filter($name, $value, ...$args)
    {
        if (isset($this->filters[$name])) {
            return ($this->filters[$name])($value, ...$args);
        }
        throw new \RuntimeException("Filter not defined: $name");
    }

    /**
     * Render a template with given context
     *
     * @param string $template
     * @param array $context
     * @return string
     */
    public function render(string $template, array $context = [])
    {
        $compiled = $this->compile($template);
        $vars     = array_merge($this->globals, $context);
        $funcs    = $this->functions;
        $sections = [];
        $extends  = null;

        ob_start();

        try {
            // Sandboxed execution with limited variables
            (function () use ($compiled, $vars, $funcs, &$sections, &$extends) {
                extract($vars, EXTR_SKIP);

                $__fn      = $funcs;
                $__sections = &$sections;
                $__extends  = &$extends;
                $__this     = $this; // allow calling escape() etc. if needed

                include $compiled;
            })();

            $output = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        // Handle @extends
        if ($extends) {
            $parent_compiled = $this->compile($extends);
            ob_start();

            try {
                (function () use ($parent_compiled, $vars, $funcs, &$sections) {
                    extract($vars, EXTR_SKIP);
                    $__fn       = $funcs;
                    $__sections = $sections;
                    include $parent_compiled;
                })();

                return ob_get_clean();
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
        }

        return $output;
    }

    /**
     * Compile a template to a cached PHP file
     *
     * @param string $template
     * @return string Path to compiled PHP file
     */
    public function compile(string $template)
    {
        $tpl_path = $this->resolve_template_path($template);
        if (!file_exists($tpl_path)) {
            throw new \RuntimeException("Template not found: $template");
        }

        $cache_file = $this->cache_path . md5($tpl_path . filemtime($tpl_path)) . '.php';

        if (!file_exists($cache_file) || filemtime($cache_file) < filemtime($tpl_path)) {
            $source   = file_get_contents($tpl_path);
            $compiled = $this->compile_string($source, $tpl_path);
            file_put_contents($cache_file, $compiled);
            chmod($cache_file, self::CACHE_PERMISSIONS);
        }

        return $cache_file;
    }

    /**
     * Resolve template name to a file path
     *
     * @param string $template
     * @return string
     */
    public function resolve_template_path(string $template)
    {
        $tpl = str_replace(['..', './', '\\'], ['', '', '/'], $template); // basic sanitization
        $tpl = ltrim($tpl, '/');

        $candidates = [
            $this->templates_path . $tpl,
            $this->templates_path . $tpl . '.ember.php',
            $this->templates_path . $tpl . '.php',
            $this->templates_path . $tpl . '.html',
            $this->templates_path . $tpl . '.tpl',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                $real = realpath($path);
                $base = realpath($this->templates_path);

                if ($real === false || strpos($real, $base) !== 0) {
                    throw new \RuntimeException("Template path traversal attempt detected: $template");
                }
                return $real;
            }
        }

        throw new \RuntimeException("Template not found: $template");
    }

    /**
     * Compile template source code to PHP code
     *
     * @param string $source
     * @param string $tpl_path
     * @return string
     */
    public function compile_string(string $source, string $tpl_path = ''): string
    {
        // Extends, Sections, Yield, Show
        $source = preg_replace('/@extends\([\'"](.+?)[\'"]\)/', '<?php $__extends = \'$1\'; ?>', $source);

        $source = preg_replace('/@section\([\'"](.+?)[\'"]\)/', '<?php $__sectionName = \'$1\'; ob_start(); ?>', $source);
        $source = preg_replace('/@endsection/', '<?php $__sections[$__sectionName] = ob_get_clean(); unset($__sectionName); ?>', $source);
        $source = preg_replace('/@show/', '<?php if (isset($__sectionName)) { $__sections[$__sectionName] = ob_get_clean(); echo $__sections[$__sectionName]; unset($__sectionName); } ?>', $source);
        $source = preg_replace('/@yield\([\'"](.+?)[\'"]\)/', '<?php echo $__sections[\'$1\'] ?? ""; ?>', $source);

        // Safer @include
        $source = preg_replace_callback('/@include\([\'"](.+?)[\'"]\)/', function ($m) {
            $tplName = preg_replace('/[^a-zA-Z0-9_\.\/-]/', '', $m[1]);
            return '<?php 
                $tplName = "' . addslashes($tplName) . '";
                $vars = array_diff_key(get_defined_vars(), array_flip(["__sections","__fn","__extends","__this"]));
                echo $this->render($tplName, $vars); 
            ?>';
        }, $source);

        // Raw echo {!! !!}
        $source = preg_replace('/\{!!\s*(.*?)\s*!!\}/s', '<?php echo $1; ?>', $source);

        // === FIXED ESCAPED ECHO {{ expression | filters }} ===
        $source = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/s', function ($m) {
            $expr = trim($m[1]);

            // Support function call: {{ upper(name) }}
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*\((.*)\)$/s', $expr, $fnMatch)) {
                $fnName = $fnMatch[1];
                $args   = $fnMatch[2];
                return "<?php echo \$this->escape((\$__fn['$fnName'])($args)); ?>";
            }

            // Variable with filters: {{ var|filter1|filter2('arg') }}
            $parts = preg_split('/\s*\|\s*/', $expr);
            $var   = array_shift($parts);

            // If it starts with quote, treat as string literal
            if (preg_match('/^([\'"])(.*)\1$/', trim($var), $strMatch)) {
                $var = var_export($strMatch[2], true);   // Safe string export
            } else {
                $var = '$' . ltrim(trim($var), '$');
            }

            foreach ($parts as $filter) {
                $filter = trim($filter);

                if ($filter === 'upper') {
                    $var = "strtoupper($var)";
                } elseif ($filter === 'lower') {
                    $var = "strtolower($var)";
                } elseif ($filter === 'raw') {
                    return "<?php echo $var; ?>";
                } elseif ($filter === 'escape' || $filter === 'e') {
                    $var = "\$this->escape($var)";
                } elseif ($filter === 'nl2br') {
                    $var = "nl2br((string) $var)";
                } elseif ($filter === 'striptags') {
                    $var = "strip_tags((string) $var)";
                } elseif ($filter === 'trim') {
                    $var = "trim((string) $var)";
                } else {
                    // Custom filter with optional arguments
                    if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)(?:\((.*)\))?$/', $filter, $fMatch)) {
                        $fName = $fMatch[1];
                        $fArgs = isset($fMatch[2]) ? ', ' . trim($fMatch[2]) : '';
                        $var = "\$this->apply_filter('$fName', $var$fArgs)";
                    } else {
                        $var = "\$this->apply_filter('$filter', $var)";
                    }
                }
            }

            return "<?php echo \$this->escape($var); ?>";
        }, $source);

        // Control structures (kept simple and safe)
        $source = preg_replace_callback('/@if\s*\((.+?)\)/s', fn($m) => "<?php if ({$m[1]}): ?>", $source);
        $source = preg_replace_callback('/@elseif\s*\((.+?)\)/s', fn($m) => "<?php elseif ({$m[1]}): ?>", $source);
        $source = preg_replace('/@else\b/', '<?php else: ?>', $source);
        $source = preg_replace('/@endif\b/', '<?php endif; ?>', $source);

        $source = preg_replace_callback('/@foreach\s*\((.+?)\)/s', fn($m) => "<?php foreach ({$m[1]}): ?>", $source);
        $source = preg_replace('/@endforeach/', '<?php endforeach; ?>', $source);

        $source = preg_replace_callback('/@for\s*\((.+?)\)/s', fn($m) => "<?php for ({$m[1]}): ?>", $source);
        $source = preg_replace('/@endfor/', '<?php endfor; ?>', $source);

        $source = preg_replace_callback('/@while\s*\((.+?)\)/s', fn($m) => "<?php while ({$m[1]}): ?>", $source);
        $source = preg_replace('/@endwhile/', '<?php endwhile; ?>', $source);

        // @php blocks - disabled by default for security
        if ($this->enable_php_blocks ?? false) {
            $source = str_replace(['@php', '@endphp'], ['<?php ', '?>'], $source);
        } else {
            $source = preg_replace('/@php[\s\S]*?@endphp/', '<!-- PHP blocks are disabled for security -->', $source);
        }

        $header = "<?php // Compiled Ember template: " . basename($tpl_path) . " | " . date('Y-m-d H:i:s') . " ?>\n";
        return $header . $source;
    }
    
    /**
     * Clear the compiled template cache
     *
     * @return void
     */
    public function clear_cache(): void
    {
        $files = glob($this->cache_path . '*.php');
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
