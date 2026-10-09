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
 * Security_headers
 * - provides security related HTTP headers for better security
 */
class Security_headers
{
    /** @var array */
    protected $config = [
        'csp_enabled' => false, // enable/disable Content-Security-Policy
        'csp_report_only'    => false,
        'report_uri'         => '',
        'trust_proxy'        => false,

        'script_src'         => [],
        'style_src'          => [],
        'font_src'           => [],
        'img_src'            => [],
        'connect_src'        => [],
        'frame_src'          => [],

        'allow_inline_style_attr'  => true,
        'allow_inline_script_attr' => false,

        'hsts_max_age'       => 31536000,
        'hsts_subdomains'    => true,
        'frame_options'      => 'DENY',
        'referrer_policy'    => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=(), payment=()',
        'coop'               => 'same-origin',   // '' to skip (e.g. OAuth popups)

        'api_max_body'       => 1048576,

        'cors' => [
            // exact origins. Leave empty to fall back to config_item('allow_origin')
            'allowed_origins' => [],
            'methods'         => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'headers'         => 'Authorization, Content-Type, X-Requested-With',
            'expose_headers'  => 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, Retry-After',
            'credentials'     => false,          // only needed for cookie auth
            'max_age'         => 600,
        ],

        'frame_ancestors' => "'none'",     // or "'self'" to allow framing your own pages
        'extra_headers'   => [],
    ];

    /** @var string */
    protected static $nonce = null;

    /**
     * Security_headers constructor.
     * @param array $overrides
     * @throws Exception
     */
    public function __construct(array $overrides = [])
    {
        $file = function_exists('config_item') ? config_item('security_headers') : null;
        if (is_array($file)) {
            $this->config = array_replace_recursive($this->config, $file);
        }
        $this->config = array_replace_recursive($this->config, $overrides);
    }

    /**
     * Returns a random nonce
     * 
     * @return string
     */
    public static function nonce()
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }

    /**
     * Returns the maximum body size for API requests
     * 
     * @return int
     */
    public function max_body()
    {
        return (int) $this->config['api_max_body'];
    }

    /**
     * Applies security headers
     * 
     * @return $this
     */
    public function apply()
    {
        if (!defined('CSP_NONCE')) define('CSP_NONCE', self::nonce());
        if (PHP_SAPI === 'cli' || headers_sent()) return $this;
        $c = $this->config;
        if ($c['csp_enabled']) {
            $n = "'nonce-" . self::nonce() . "'";

            $csp = [
                "default-src 'self'",
                'script-src ' . $this->sources(["'self'", $n], $c['script_src']),
                'script-src-attr ' . ($c['allow_inline_script_attr'] ? "'unsafe-inline'" : "'none'"),
                'style-src ' . $this->sources(["'self'", $n], $c['style_src']),
                'style-src-attr ' . ($c['allow_inline_style_attr'] ? "'unsafe-inline'" : "'none'"),
                'img-src '     . $this->sources(["'self'", 'data:'], $c['img_src']),
                'font-src '    . $this->sources(["'self'"], $c['font_src']),
                'connect-src ' . $this->sources(["'self'"], $c['connect_src']),
                'frame-src '   . $this->sources(["'self'"], $c['frame_src']),
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors " . $c['frame_ancestors'],
            ];
            if ($this->is_https()) $csp[] = 'upgrade-insecure-requests';
            if ($c['report_uri'] !== '') $csp[] = 'report-uri ' . $c['report_uri'];

            $name = $c['csp_report_only'] ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            header($name . ': ' . implode('; ', $csp));
        }
        header('X-Frame-Options: ' . $c['frame_options']);
        $this->common();
        return $this;
    }

    /**
     * Applies security headers for API requests
     * 
     * @return $this
     */
    public function apply_api()
    {
        if (headers_sent()) return $this;

        header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");
        header('Cache-Control: no-store, max-age=0');
        header('Pragma: no-cache');
        header('X-Frame-Options: DENY');
        header('Cross-Origin-Resource-Policy: same-origin');

        $this->common();
        $this->cors();
        return $this;
    }

    /**
     * Guard an API request for method, body size, and content type
     *
     * @param array|null $methods
     * @param int|null $max_body
     * @return $this
     */
    public function api_guard($methods = null, $max_body = null)
    {
        $method   = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $max_body = $max_body ?? $this->max_body();
        $length   = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

        if (is_array($methods)) {
            $methods[] = 'OPTIONS';
            if (!in_array($method, $methods, true)) {
                header('Allow: ' . implode(', ', array_unique($methods)));
                $this->fail(405, 'Method not allowed');
            }
        }

        if ($length > $max_body) {
            $this->fail(413, 'Payload too large');
        }

        if ($length > 0 && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $type = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
            $ok = strpos($type, 'application/json') === 0
               || strpos($type, 'multipart/form-data') === 0
               || strpos($type, 'application/x-www-form-urlencoded') === 0;
            if (!$ok) $this->fail(415, 'Unsupported media type');
        }
        return $this;
    }

    /**
     * Applies CORS headers
     *
     * @return $this
     */
    public function cors()
    {
        if (headers_sent()) return $this;

        $c       = $this->config['cors'];
        $origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
        $list    = $this->allowed_origins();

        header('Vary: Origin');
        if ($origin === '') return $this;

        $exact    = in_array($origin, $list, true);
        $wildcard = in_array('*', $list, true);

        if ($exact) {
            header('Access-Control-Allow-Origin: ' . $origin);
            if ($c['credentials']) header('Access-Control-Allow-Credentials: true');
        } elseif ($wildcard) {
            header('Access-Control-Allow-Origin: *');   // never combined with credentials
        }

        if ($exact || $wildcard) {
            header('Access-Control-Expose-Headers: ' . $c['expose_headers']);
        }

        $is_preflight = strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS'
                     && isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']);

        if ($is_preflight) {
            if (!$exact && !$wildcard) $this->fail(403, 'Origin not allowed');
            header('Access-Control-Allow-Methods: ' . $c['methods']);
            header('Access-Control-Allow-Headers: ' . $c['headers']);
            header('Access-Control-Max-Age: ' . (int) $c['max_age']);
            http_response_code(204);
            exit;
        }
        return $this;
    }

    /**
     * Common headers for both web and API
     *
     * @return void
     */
    protected function common()
    {
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: ' . $this->config['referrer_policy']);
        header('Permissions-Policy: ' . $this->config['permissions_policy']);
        if ($this->config['coop'] !== '') {
            header('Cross-Origin-Opener-Policy: ' . $this->config['coop']);
        }
        header('X-Permitted-Cross-Domain-Policies: none');
        foreach ((array) $this->config['extra_headers'] as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($this->is_https()) {
            $h = 'max-age=' . (int) $this->config['hsts_max_age'];
            if ($this->config['hsts_subdomains']) $h .= '; includeSubDomains';
            header('Strict-Transport-Security: ' . $h);
        }
    }

    /**
     * Returns the list of allowed origins for CORS
     *
     * @return array
     */
    protected function allowed_origins()
    {
        $list = $this->config['cors']['allowed_origins'];

        if (empty($list) && function_exists('config_item')) {
            $legacy = config_item('allow_origin');   // your existing api config key
            if (is_array($legacy)) {
                $list = $legacy;
            } elseif (is_string($legacy) && $legacy !== '') {
                $list = [$legacy];
            }
        }

        return array_map(function ($o) { return rtrim((string) $o, '/'); }, (array) $list);
    }

    /**
     * Merges and returns unique sources for CSP directives
     *
     * @param array $base
     * @param array $extra
     * @return string
     */
    protected function sources(array $base, array $extra)
    {
        return implode(' ', array_unique(array_merge($base, $extra)));
    }

    /**
     * Returns true if the request is HTTPS
     *
     * @return bool
     */
    protected function is_https()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') return true;
        if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) return true;
        return $this->config['trust_proxy']
            && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    /**
     * Sends an error response and exits
     *
     * @param int $code
     * @param string $message
     * @return void
     */
    protected function fail($code, $message)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message, 'status' => $code]);
        exit;
    }
}

/**
 * Returns a random nonce for CSP
 *
 * @return string
 */
if (!function_exists('csp_nonce')) {
    function csp_nonce()
    {
        return Security_headers::nonce();
    }
}