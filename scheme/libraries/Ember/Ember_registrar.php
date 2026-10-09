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
*  Class Ember_registrar
* ------------------------------------------------------
 */
class Ember_registrar
{
    /**
     * Register globals, filters, and functions with the Ember engine
     *
     * @param Ember $engine
     * @return void
     */
    public function register(Ember $engine)
    {
        $lava = lava_instance();

        // Load required helpers
        $lava->call->helper('url');
        $lava->call->helper('form');   // for csrf_field if available

        /** ----------------------------------------------------------
         *  GLOBALS
         * ---------------------------------------------------------- */
        $engine->add_global('app_name', config_item('app_name') ?? 'LavaLust App');
        $engine->add_global('base_url', base_url());
        $engine->add_global('site_url', site_url());

        // CSP nonce (if you implemented the Security_headers)
        $engine->add_global('csp_nonce', csp_nonce());

        /** ----------------------------------------------------------
         *  FILTERS
         * ---------------------------------------------------------- */

        // String manipulation
        $engine->add_filter('upper', fn($v) => strtoupper((string) $v));
        $engine->add_filter('lower', fn($v) => strtolower((string) $v));
        $engine->add_filter('title', fn($v) => ucwords(strtolower((string) $v)));
        $engine->add_filter('capitalize', fn($v) => ucfirst((string) $v));
        $engine->add_filter('trim', fn($v) => trim((string) $v));
        $engine->add_filter('reverse', fn($v) => is_array($v) ? array_reverse($v) : strrev((string) $v));

        // HTML & escaping
        $engine->add_filter('escape', fn($v) => $engine->escape($v));           // use engine's improved escape
        $engine->add_filter('e', fn($v) => $engine->escape($v));
        $engine->add_filter('raw', fn($v) => $v);                               // dangerous – use carefully
        $engine->add_filter('nl2br', fn($v) => nl2br((string) $v));
        $engine->add_filter('striptags', fn($v) => strip_tags((string) $v));

        // Array & string utilities
        $engine->add_filter('length', fn($v) => is_array($v) ? count($v) : strlen((string) $v));
        $engine->add_filter('default', fn($v, $default = '') => $v ?: $default);
        $engine->add_filter('join', fn($v, $separator = ', ') => is_array($v) ? implode($separator, $v) : $v);
        $engine->add_filter('slice', fn($v, $start, $length = null) => 
            is_array($v) ? array_slice($v, $start, $length) : substr((string) $v, $start, $length)
        );

        // Number formatting
        $engine->add_filter('number_format', fn($v, $decimals = 0) => number_format((float) $v, $decimals));
        $engine->add_filter('abs', fn($v) => abs((float) $v));
        $engine->add_filter('round', fn($v, $precision = 0) => round((float) $v, $precision));

        // Date & time
        $engine->add_filter('date', fn($v, $format = 'Y-m-d H:i:s') => 
            date($format, is_numeric($v) ? (int) $v : strtotime((string) $v))
        );

        // JSON & replacement
        $engine->add_filter('json', fn($v, $pretty = false) => json_encode($v, $pretty ? JSON_PRETTY_PRINT : 0));
        $engine->add_filter('replace', fn($v, $search = '', $replace = '') => str_replace($search, $replace, (string) $v));

        // Custom / locale-specific
        $engine->add_filter('money', fn($v, $symbol = '₱') => $symbol . number_format((float) $v, 2));
        $engine->add_filter('slug', function($v) {
            $v = strtolower(trim((string) $v));
            $v = preg_replace('/[^a-z0-9\s-]/', '', $v);           // remove special chars
            $v = preg_replace('/[\s-]+/', '-', $v);                // collapse spaces/dashes
            return trim($v, '-');
        });
        $engine->add_filter('pluralize', fn($count, $word) => 
            $count == 1 ? $word : $word . 's'
        );

        /** ----------------------------------------------------------
         *  FUNCTIONS
         * ---------------------------------------------------------- */

        // URL & asset helpers
        $engine->add_function('url', fn($path = '') => '/' . ltrim((string) $path, '/'));
        $engine->add_function('asset', fn($path) => base_url('public/' . ltrim((string) $path, '/')));
        $engine->add_function('site_url', fn($path = '') => site_url(ltrim((string) $path, '/')));
        $engine->add_function('base_url', fn($path = '') => base_url(ltrim((string) $path, '/')));
        $engine->add_function('active', fn($uri) => active($uri) ?? '');   // assuming your helper returns class or empty

        // Config & session
        $engine->add_function('config', fn($key) => config_item($key));
        $engine->add_function('session', function($key = null) {
            $lava = lava_instance();
            if ($key === null) {
                return $lava->session->get_userdata() ?? [];
            }
            $data = $lava->session->get_userdata($key);
            return is_array($data) ? ($data[$key] ?? null) : $data;
        });

        // String utilities
        $engine->add_function('upper', fn($str) => strtoupper((string) $str));
        $engine->add_function('repeat', fn($str, $times) => str_repeat((string) $str, (int) $times));

        // Date / time
        $engine->add_function('now', fn() => date('Y-m-d H:i:s'));
        $engine->add_function('date', fn($format = 'Y-m-d H:i:s', $timestamp = null) => 
            date($format, $timestamp ?? time())
        );

        // Template & debugging
        $engine->add_function('include', fn($template, $vars = []) => $engine->render($template, (array) $vars));
        $engine->add_function('dump', function($var, $die = false) {
            $output = '<pre style="background:#f4f4f4;padding:10px;border:1px solid #ddd;">' 
                    . htmlspecialchars(print_r($var, true), ENT_QUOTES, 'UTF-8') 
                    . '</pre>';
            if ($die) {
                echo $output;
                exit;
            }
            return $output;
        });

        // Security & forms (important!)
        $engine->add_function('csrf_field', fn() => csrf_field() ?? '');
        $engine->add_function('old', function($key, $default = '') {
            // Implement based on your framework's flash/input handling
            $lava = lava_instance();
            return $lava->input->post($key) ?? $default;   // adjust if you have old() helper
        });

        // CSP support
        $engine->add_function('nonce', fn() => csp_nonce());

        // Extra useful functions
        $engine->add_function('asset_version', function($path) {
            $fullPath = PUBLIC_DIR . ltrim($path, '/');   // adjust path constant if needed
            if (file_exists($fullPath)) {
                return base_url($path) . '?v=' . filemtime($fullPath);
            }
            return base_url($path);
        });
    }
}