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
*  Class API
* ------------------------------------------------------
 */
class Api
{
    /**
     * LavaLust Super Object
     *
     * @var object
     */
    private $_lava;

    /**
     * Api Refresh Token Table
     *
     * @var string
     */
    protected $refresh_token_table;

    /**
     * Api Payload Token Expiration
     *
     * This is used for Payload Token Expiration.
     * Default is 900 seconds (15 minutes).
     *
     * @var integer
     */
    protected $payload_token_expiration;

    /**
     * Api Refresh Token Expiration
     *
     * This is used for Refresh Token Expiration.
     * Default is 604800 seconds (7 days).
     *
     * @var integer
     */
    protected $refresh_token_expiration;

    /**
     * Allow Origin
     *
     * @var string
     */
    protected $allow_origin;

    /**
     * Secret Code
     *
     * @var string
     */
    private $jwt_secret;

    /**
     * Refresh Token
     *
     * @var string
     */
    private $refresh_token_key;

    /**
     * JWT Issuer
     *
     * @var string
     */
    protected $jwt_issuer;

    /**
     * JWT Audience
     *
     * @var string
     */
    protected $jwt_audience;

    /**
     * Rate Limiting
     *
     * @var boolean
     */
    protected $rate_limit_enabled;

    /**
     * Rate Limit Requests
     *
     * @var integer
     */
    protected $rate_limit_requests;

    /**
     * Rate Limit Seconds
     *
     * @var integer
     */
    protected $rate_limit_seconds;

    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_lava->call->library('cache');
        $this->_lava->config->load('api');

        if (!config_item('api_helper_enabled')) {
            show_error('Api Helper is disabled or set up incorrectly.');
        }

        // Load config
        $this->refresh_token_table      = config_item('refresh_token_table') ?? $this->refresh_token_table;
        $this->payload_token_expiration = (int) (config_item('payload_token_expiration') ?? $this->payload_token_expiration);
        $this->refresh_token_expiration = (int) (config_item('refresh_token_expiration') ?? $this->refresh_token_expiration);
        $this->jwt_secret               = config_item('jwt_secret');
        $this->refresh_token_key        = config_item('refresh_token_key');
        $this->allow_origin             = config_item('allow_origin');

        // JWT config
        $this->jwt_issuer              = config_item('jwt_issuer') ?? $this->jwt_issuer;
        $this->jwt_audience            = config_item('jwt_audience') ?? $this->jwt_audience;

        // Rate limit config
        $this->rate_limit_enabled   = (bool) (config_item('rate_limit_enabled') ?? true);
        $this->rate_limit_requests  = (int)  (config_item('rate_limit_requests') ?? $this->rate_limit_requests);
        $this->rate_limit_seconds   = (int)  (config_item('rate_limit_seconds') ?? $this->rate_limit_seconds);

        if (empty($this->jwt_secret) || strlen($this->jwt_secret) < 32) {
            show_error('JWT secret is missing or too weak. Use at least 32 random characters.');
        }
        if (empty($this->refresh_token_key) || strlen($this->refresh_token_key) < 32) {
            show_error('Refresh token key is missing or too weak.');
        }

        $this->handle_cors();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    // --------------------------
    // Basic Utilities
    // --------------------------
    /**
     * handle cors
     *
     * @return void
     */
    public function handle_cors()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (is_array($this->allow_origin)) {
            $allowed = in_array($origin, $this->allow_origin, true);
        } else {
            $allowed = $this->allow_origin === '*' || $this->allow_origin === $origin;
        }

        if ($allowed && $origin) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
        } elseif ($this->allow_origin === '*') {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, X-RateLimit-*');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Max-Age: 3600');
        header('Content-Type: application/json; charset=UTF-8');
    }

    /**
     * API body
     *
     * @return void
     */
    public function body()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            return is_array($input) ? $this->sanitize_input($input) : [];
        }

        if ($_POST) {
            return $this->sanitize_input($_POST);
        }

        parse_str(file_get_contents('php://input'), $formData);
        return $this->sanitize_input($formData ?? []);
    }

    /**
     * get_query_params
     *
     * @return void
     */
    public function get_query_params()
    {
        return $this->sanitize_input($_GET);
    }

    /**
     * sanitize_input
     *
     * @param array $data
     * @return array
     */
    private function sanitize_input($data)
    {
        array_walk_recursive($data, function(&$value) {
            if (is_string($value)) {
                $value = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        });
        return $data;
    }

    /**
     * require_method
     *
     * @param string $method
     * @return void
     */
    public function require_method(string $method)
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->respond_error("Method Not Allowed", 405);
        }
    }

    /**
     * rate_limit
     *
     * @param string|null $key
     * @param integer|null $requests
     * @param integer|null $seconds
     * @return void
     */
    public function rate_limit($key = null, $requests = null, $seconds = null)
    {
        if (!$this->rate_limit_enabled) {
            return;
        }

        $requests = $requests ?? $this->rate_limit_requests;
        $seconds  = $seconds  ?? $this->rate_limit_seconds;

        // Generate safe cache key (Windows-friendly)
        if ($key === null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $raw_key = 'rate_limit:' . $ip;
        } else {
            $raw_key = 'rate_limit:' . $key;
        }

        // Replace unsafe characters for Windows filenames
        $safe_key = str_replace([':', '/', '\\', '*', '?', '"', '<', '>', '|'], '_', $raw_key);
        $safe_key = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $safe_key); // extra safety

        $cache = $this->_lava->cache;

        $current      = $cache->get($safe_key);
        $window_start = $cache->get($safe_key . '_start');   // Use underscore instead of :

        $current      = is_numeric($current) ? (int)$current : 0;
        $window_start = is_numeric($window_start) ? (int)$window_start : 0;

        $now = time();

        if ($window_start === 0 || ($now - $window_start) >= $seconds) {
            // New window
            $cache->write(1, $safe_key, $seconds);
            $cache->write($now, $safe_key . '_start', $seconds);
            $remaining = $requests - 1;
        } else {
            if ($current >= $requests) {
                $reset_time = $window_start + $seconds;
                $this->respond_rate_limit_exceeded($requests, $current, $reset_time);
            }

            $cache->write($current + 1, $safe_key, $seconds);
            $remaining = $requests - ($current + 1);
        }

        // Rate limit headers
        header("X-RateLimit-Limit: $requests");
        header("X-RateLimit-Remaining: $remaining");
        header("X-RateLimit-Reset: " . ($window_start + $seconds));
    }

    /**
     * respond_rate_limit_exceeded
     *
     * @param integer $limit
     * @param integer $used
     * @param integer $reset_time
     * @return void
     */
    private function respond_rate_limit_exceeded($limit, $used, $reset_time)
    {
        $retry_after = max(0, $reset_time - time());
        header("Retry-After: $retry_after");

        $this->respond([
            'error'       => 'Too many requests. Please try again later.',
            'limit'       => $limit,
            'used'        => $used,
            'remaining'   => 0,
            'reset_at'    => date('c', $reset_time),
            'retry_after' => $retry_after
        ], 429);
    }

    /**
     * respond
     *
     * @param mixed $data
     * @param integer $code
     * @return void
     */
    public function respond($data, $code = 200)
    {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * respond_error
     *
     * @param string $message
     * @param integer $code
     * @return void
     */
    public function respond_error($message, $code = 400)
    {
        $this->respond(['error' => $message, 'status' => $code], $code);
    }

    /**
     * base64UrlEncode
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * base64UrlDecode
     *
     * @param string $data
     * @return string
     */
    private function base64UrlDecode($data)
    {
        $pad = strlen($data) % 4;
        if ($pad) $data .= str_repeat('=', 4 - $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }

    // --------------------------
    // Auth: JWT
    // --------------------------
    /**
     * encode_jwt
     *
     * @param array $payload
     * @return void
     */
    public function encode_jwt($payload)
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headerEnc = $this->base64UrlEncode(json_encode($header));

        $now = time();
        $payload = array_merge([
            'iat' => $now,
            'exp' => $now + $this->payload_token_expiration,
            'iss' => $this->jwt_issuer,
            'aud' => $this->jwt_audience,
            'jti' => bin2hex(random_bytes(16))
        ], $payload);

        $payloadEnc = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$headerEnc.$payloadEnc", $this->jwt_secret, true);
        $sigEnc = $this->base64UrlEncode($signature);

        return "$headerEnc.$payloadEnc.$sigEnc";
    }

    /**
     * decode_jwt
     *
     * @param string $token
     * @return void
     */
    public function decode_jwt($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$headerEnc, $payloadEnc, $sigEnc] = $parts;

        $header = json_decode($this->base64UrlDecode($headerEnc), true);
        if (($header['alg'] ?? '') !== 'HS256') return null;

        $validSig = hash_hmac('sha256', "$headerEnc.$payloadEnc", $this->jwt_secret, true);
        if (!hash_equals($this->base64UrlEncode($validSig), $sigEnc)) return null;

        return json_decode($this->base64UrlDecode($payloadEnc), true);
    }

    /**
     * validate_jwt
     *
     * @param string $token
     * @return void
     */
    public function validate_jwt($token)
    {
        $payload = $this->decode_jwt($token);
        if (!$payload) return null;

        if (!isset($payload['sub'], $payload['exp'], $payload['iat'])) return null;
        if ($payload['exp'] < time() || ($payload['iat'] ?? 0) > time()) return null;
        if (($payload['iss'] ?? '') !== $this->jwt_issuer || ($payload['aud'] ?? '') !== $this->jwt_audience) return null;

        return $payload;
    }

    /**
     * get_bearer_token
     *
     * @return void
     */
    public function get_bearer_token()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (!$header && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? '';
        }

        return preg_match('/Bearer\s(\S+)/i', $header, $matches) ? $matches[1] : null;
    }


    /**
     * require_jwt
     *
     * @return void
     */
    public function require_jwt()
    {
        $token = $this->get_bearer_token();
        $payload = $this->validate_jwt($token ?? '');

        if (!$payload) {
            $this->respond_error('Unauthorized', 401);
        }

        return $payload;
    }

    // --------------------------
    // Auth: Token System
    // --------------------------
    /**
     * issue_tokens
     *
     * @param array $user_data
     * @return void
     */
    public function issue_tokens($user_data)
    {
        $user_id = $user_data['id'];
        $now = time();
        $scopes = $user_data['scopes'] ?? ['read'];

        $access_payload = [
            'sub'   => $user_id,
            'role'  => $user_data['role'] ?? 'user',
            'scopes'=> $scopes,
        ];

        $refresh_payload = [
            'sub'  => $user_id,
            'type' => 'refresh',
            'jti'  => bin2hex(random_bytes(16)),
        ];

        $access_token  = $this->encode_jwt($access_payload);
        $refresh_token = $this->encode_jwt($refresh_payload); // Raw for client

        // Hash for DB storage (secure + prevents exposure on DB breach)
        $hashed_refresh = hash_hmac('sha256', (string) $refresh_token, $this->refresh_token_key);

        $this->cleanup_expired_refresh_tokens($user_id);

        $expires_at = date('Y-m-d H:i:s', $now + $this->refresh_token_expiration);

        $this->_lava->db->raw(
            "INSERT INTO {$this->refresh_token_table} (user_id, token, expires_at, jti) 
             VALUES (?, ?, ?, ?)",
            [$user_id, $hashed_refresh, $expires_at, $refresh_payload['jti']]
        );

        return [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in'   => $this->payload_token_expiration,
            'token_type'   => 'Bearer'
        ];
    }

    /**
     * refresh_access_token
     *
     * @param string $refresh_token
     * @return void
     */
    public function refresh_access_token($refresh_token)
    {
        $payload = $this->validate_jwt($refresh_token);
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            $this->respond_error('Invalid refresh token', 403);
        }

        $hashed = hash_hmac('sha256', $refresh_token, $this->refresh_token_key);

        $stmt = $this->_lava->db->raw(
            "SELECT * FROM {$this->refresh_token_table} 
             WHERE token = ? AND expires_at > NOW() LIMIT 1",
            [$hashed]
        );
        $found = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$found) {
            $this->respond_error('Refresh token expired or revoked', 403);
        }

        // Revoke old + rotate (best practice)
        $this->revoke_refresh_token($refresh_token);

        $new_tokens = $this->issue_tokens(['id' => $payload['sub']]);

        $this->respond([
            'message' => 'Tokens refreshed successfully',
            'tokens'  => $new_tokens
        ]);
    }

    /**
     * revoke_refresh_token
     *
     * @param string $refresh_token
     * @return void
     */
    public function revoke_refresh_token($refresh_token)
    {
        $hashed = hash_hmac('sha256', $refresh_token, $this->refresh_token_key);
        $this->_lava->db->raw(
            "DELETE FROM {$this->refresh_token_table} WHERE token = ?",
            [$hashed]
        );
    }

    /**
     * cleanup_expired_refresh_tokens
     *
     * @param integer|null $user_id
     * @return void
     */
    public function cleanup_expired_refresh_tokens($user_id = null): void
    {
        $sql = "DELETE FROM {$this->refresh_token_table} WHERE expires_at < NOW()";
        $params = [];

        if ($user_id !== null) {
            $sql .= " AND user_id = ?";
            $params[] = $user_id;
        }

        $this->_lava->db->raw($sql, $params);
    }


    // --------------------------
    // Basic Auth Support
    // --------------------------
    /**
     * check_basic_auth
     *
     * @param string $valid_user
     * @param string $valid_pass
     * @return void
     */
    public function check_basic_auth($valid_user, $valid_pass)
    {
        $user = $_SERVER['PHP_AUTH_USER'] ?? '';
        $pass = $_SERVER['PHP_AUTH_PW'] ?? '';
        return hash_equals($user, $valid_user) && hash_equals($pass, $valid_pass);
    }

    /**
     * require_basic_auth
     *
     * @param string $valid_user
     * @param string $valid_pass
     * @return void
     */
    public function require_basic_auth($valid_user, $valid_pass)
    {
        if (!$this->check_basic_auth($valid_user, $valid_pass)) {
            header('WWW-Authenticate: Basic realm="API"');
            $this->respond_error('Unauthorized', 401);
        }
    }
}