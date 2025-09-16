<?php

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

    public function __construct()
    {
        $this->_lava = lava_instance();

        $this->_lava->config->load('api');

        if(! config_item('api_helper_enabled')) {
            show_error('Api Helper is disabled or set up incorrectly.');
        }

        $this->refresh_token_table = config_item('refresh_token_table');
        $this->payload_token_expiration = config_item('payload_token_expiration');
        $this->refresh_token_expiration = config_item('refresh_token_expiration');
        $this->jwt_secret = config_item('jwt_secret');
        $this->refresh_token_key = config_item('refresh_token_key');
        $this->allow_origin = config_item('allow_origin');

        //Handle CORS
        $this->handle_cors();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
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
        header("Access-Control-Allow-Origin: {$this->allow_origin}");
        header("Access-Control-Allow-Headers: Authorization, Content-Type");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Content-Type: application/json; charset=UTF-8");
    }

    /**
     * API body
     *
     * @return void
     */
    public function body()
    {
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

        // JSON input
        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents("php://input"), true);
            return is_array($input) ? $input : [];
        }

        // Form data fallback
        if ($_POST) {
            return $_POST;
        }

        // Raw fallback for form-encoded bodies
        parse_str(file_get_contents("php://input"), $formData);
        return $formData;
    }

    /**
     * get_query_params
     *
     * @return void
     */
    public function get_query_params()
    {
        return $_GET;
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
     * respond
     *
     * @param mixed $data
     * @param integer $code
     * @return void
     */
    public function respond($data, $code = 200)
    {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    public function respond_error($message, $code = 400)
    {
        $this->respond(['error' => $message], $code);
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
    public function encode_jwt(array $payload)
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", $this->jwt_secret, true);
        return "$header.$payload." . base64_encode($signature);
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
        if (count($parts) !== 3) return false;
        [$header, $payload, $signature] = $parts;

        $valid_sig = base64_encode(hash_hmac('sha256', "$header.$payload", $this->jwt_secret, true));
        if (!hash_equals($valid_sig, $signature)) return false;

        return json_decode(base64_decode($payload), true);
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
        if (!$payload) return false;

        if (!isset($payload['sub'], $payload['iat'], $payload['exp'])) return false;
        if ($payload['exp'] < time()) return false;

        return $payload;
    }

    /**
     * get_bearer_token
     *
     * @return void
     */
    public function get_bearer_token()
    {
        $header = null;

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        }
        elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $header = $headers['Authorization'];
            }
        }

        if ($header && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }


    /**
     * require_jwt
     *
     * @return void
     */
    public function require_jwt()
    {
        $token = $this->get_bearer_token();
        $payload = $this->validate_jwt($token);
        if (!$payload) $this->respond_error('Unauthorized', 401);
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
    public function issue_tokens(array $user_data)
    {
        $user_id = $user_data['id'];
        $now = time();
        $scopes = $user_data['scopes'] ?? ['read'];

        $access_payload = [
            'sub' => $user_id,
            'role' => $user_data['role'] ?? 'user',
            'scopes' => $scopes,
            'iat' => $now,
            'exp' => $now + 900
        ];

        $refresh_payload = [
            'sub' => $user_id,
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + 604800
        ];

        $access_token = $this->encode_jwt($access_payload);
        $refresh_token_raw = $this->encode_jwt($refresh_payload);
        $refresh_token_encrypted = $this->encrypt_token($refresh_token_raw);

        $this->cleanup_expired_refresh_tokens($user_id);

        $this->_lava->db->raw('insert into refresh_tokens (user_id, token, expires_at) VALUES (?, ?, ?)', [$user_id, $refresh_token_encrypted, date('Y-m-d H:i:s', $refresh_payload['exp'])]);

        return [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token_raw,
            'expires_in' => 900
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

        $encrypted = $this->encrypt_token($refresh_token);
        $stmt = $this->_lava->db->raw('select * from refresh_tokens WHERE token = ? LIMIT 1', [$encrypted]);
        $found = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$found || strtotime($found['expires_at']) < time()) {
            $this->respond_error('Refresh token expired or not found', 403);
        }

        $this->revoke_refresh_token($refresh_token);
        $new = $this->issue_tokens(['id' => $payload['sub']]);
        $this->respond([
            'message' => 'Token refreshed successfully',
            'tokens' => $new
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
        $encrypted = $this->encrypt_token($refresh_token);
        $this->_lava->db->raw('delete from refresh_tokens WHERE token = ?', [$encrypted]);
    }

    public function cleanup_expired_refresh_tokens($user_id)
    {
        $this->_lava->db->raw('delete from refresh_tokens WHERE user_id = ? AND expires_at < NOW()', [$user_id]);
    }

    // --------------------------
    // Refresh Token Encryption
    // --------------------------
    /**
     * encrypt_token
     *
     * @param string $token
     * @return void
     */
    private function encrypt_token($token)
    {
        $key = hash('sha256', $this->refresh_token_key);
        $iv = substr(hash('sha256', 'static_iv'), 0, 16);
        return openssl_encrypt($token, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * decrypt_token
     *
     * @param string $encrypted
     * @return void
     */
    public function decrypt_token($encrypted)
    {
        $key = hash('sha256', $this->refresh_token_key);
        $iv = substr(hash('sha256', 'static_iv'), 0, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
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
        return ($user === $valid_user && $pass === $valid_pass);
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
            header('WWW-Authenticate: Basic realm="Restricted"');
            $this->respond_error('Unauthorized', 401);
        }
    }
}