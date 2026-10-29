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

class Session {

	/** @var array */
	private $config;

	/** @var bool */
	private $match_ip;

	/** @var bool */
	private $match_fingerprint;

	/** @var array */
	private $userdata;

	/** @var int Invalid attempts allowed before locking */
	private $max_invalid_attempts;

	/** @var int Window for counting invalid attempts (seconds) */
	private $invalid_window;

	/** @var int Lock duration after too many invalid attempts (seconds) */
	private $lock_duration_invalid;

	/** @var int Max session creations per IP within the window */
	private $max_session_creations;

	/** @var int Window for counting session creations (seconds) */
	private $creation_window;

	/** @var int Lock duration after creation flood (seconds) */
	private $lock_duration_creation;

	/** @var string Path to the security JSON store */
	private $security_file;

	/** @var int Inactivity timeout (seconds) */
	private $inactivity_timeout;

	/** @var string HMAC secret for fingerprinting */
	private $hmac_secret;

	/** @var bool Set when the previous session was dropped for inactivity */
	private $inactivity_expired = false;

	/**
	 * Class constructor
	 *
	 * @throws RuntimeException
	 */
	public function __construct()
	{
		$this->config = get_config();
		$production = strtolower((string) config_item('environment')) === 'production';

		// Database session handler support
		if (($this->config['sess_driver'] ?? '') === 'database') {
			$handler = load_class('Database_session_handler', 'libraries/Session');
			session_set_save_handler($handler, true);
		}

		// Security config with safe defaults
		$this->max_invalid_attempts   = (int)($this->config['max_invalid_attempts'] ?? 5);
		$this->invalid_window         = (int)($this->config['invalid_window'] ?? 600);
		$this->lock_duration_invalid  = (int)($this->config['lock_duration_invalid'] ?? 900);
		$this->max_session_creations  = (int)($this->config['max_session_creations'] ?? 8);
		$this->creation_window        = (int)($this->config['creation_window'] ?? 60);
		$this->lock_duration_creation = (int)($this->config['lock_duration_creation'] ?? 180);
		$this->inactivity_timeout     = (int)($this->config['sess_inactivity_timeout'] ?? 1800);
		$this->match_ip               = (bool)($this->config['sess_match_ip'] ?? false);
		$this->match_fingerprint      = (bool)($this->config['sess_match_fingerprint'] ?? true);

		$this->security_file = $this->config['security_file']
			?? (ROOT_DIR . 'runtime/session/session_security.json');
		$this->_security_init();

		// HMAC secret
		$secret = (string)($this->config['session_hmac_secret'] ?? '');
		if ($secret === '') {
			if ($production) {
				throw new Exception('Session security requires a secret. Please set session_hmac_secret in your config file.');
			}
			// Non-production: persist a generated secret so fingerprints stay
			// valid across requests (a per-request random key breaks every session).
			$secret = $this->_dev_secret();
		} elseif ($production && strlen($secret) < 32) {
			throw new Exception('session_hmac_secret must be at least 32 characters in production.');
		}
		$this->hmac_secret = $secret;

		// PHP2Shell / CVE-2026-115560 hygiene: this can only be turned off in
		// php.ini / .user.ini / pool config (PHP_INI_PERDIR), not at runtime.
		if (filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN)) {
			error_log('[Session Security] session.upload_progress.enabled is ON. Set session.upload_progress.enabled=0 in php.ini (see PHP advisories for CVE-2026-109619 / CVE-2026-115560).');
		}

		// Cookie name
		$prefix = $this->config['cookie_prefix'] ?? '';
		$this->config['cookie_name'] = $prefix
			? $prefix . ($this->config['sess_cookie_name'] ?? 'lavalust_session')
			: ($this->config['sess_cookie_name'] ?? ini_get('session.name'));
		ini_set('session.name', $this->config['cookie_name']);

		// Expiration
		$this->config['sess_expiration'] = (int)($this->config['sess_expiration'] ?? ini_get('session.gc_maxlifetime') ?: 7200);
		ini_set('session.gc_maxlifetime', $this->config['sess_expiration']);

		$this->config['cookie_expiration'] = isset($this->config['cookie_expiration'])
			? (int)$this->config['cookie_expiration']
			: (($this->config['sess_expire_on_close'] ?? false) ? 0 : $this->config['sess_expiration']);

		// Cookie params. "secure" defaults to the actual transport instead of false.
		$secure = $this->config['cookie_secure'] ?? $this->_is_https();
		$this->config['cookie_secure'] = (bool) $secure;

		session_set_cookie_params([
			'lifetime' => $this->config['cookie_expiration'],
			'path'     => $this->config['cookie_path'] ?? '/',
			'domain'   => $this->config['cookie_domain'] ?? '',
			'secure'   => $this->config['cookie_secure'],
			'httponly' => true,
			'samesite' => $this->config['cookie_samesite'] ?? 'Strict'
		]);

		// Hardened PHP session settings
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_strict_mode', 1);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.cookie_httponly', 1);

		if (version_compare(PHP_VERSION, '8.4.0', '<')) {
			ini_set('session.sid_length', $this->_get_sid_length());
			ini_set('session.sid_bits_per_character', 6);
		}

		// NOTE: never change session.serialize_handler at runtime (ini_set) before
		// session_start(); that is the pattern CVE-2026-115560 abuses.

		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		$fingerprint = $this->generate_fingerprint();

		// Enforce locks BEFORE a session is started
		$lock_msg = $this->security_check_lock($ip, $fingerprint);
		if ($lock_msg !== null) {
			throw new RuntimeException($lock_msg, 429);
		}

		session_start();

		// Brand-new session? (keyed on our own marker, not on the cookie,
		// so sending random cookie values can't dodge the flood counter)
		if (empty($_SESSION['fingerprint'])) {
			$creation_msg = $this->security_track_session_creation($ip, $fingerprint);
			if ($creation_msg !== null) {
				session_destroy();
				$_SESSION = [];
				throw new RuntimeException($creation_msg, 429);
			}
			$this->_init_session_markers($fingerprint);
		} elseif ($this->match_fingerprint && !hash_equals($_SESSION['fingerprint'], $fingerprint)) {
			$this->security_log_attempt($ip, $fingerprint, 'Fingerprint mismatch');
			$this->sess_destroy();
			throw new RuntimeException('Session terminated: Fingerprint mismatch detected.');
		}

		if ($this->match_ip && isset($_SESSION['ip_address']) && !hash_equals((string)$_SESSION['ip_address'], $ip)) {
			$this->security_log_attempt($ip, $fingerprint, 'IP mismatch');
			$this->sess_destroy();
			throw new RuntimeException('Session terminated: IP address mismatch detected.');
		}
		$_SESSION['ip_address'] = $ip;

		// Inactivity: drop the old session AND start a clean one, otherwise the
		// rest of the request would write into a session that is never saved.
		if (isset($_SESSION['last_activity'])
			&& (time() - $_SESSION['last_activity']) > $this->inactivity_timeout) {

			$this->sess_destroy();
			$this->_start_fresh_session($fingerprint);
			$this->inactivity_expired = true;
		} else {
			$_SESSION['last_activity'] = time();
		}

		// Periodic regeneration (skip AJAX, which can race with other requests)
		$regenerate_time = (int)($this->config['sess_time_to_update'] ?? 300);
		$is_ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
		if (!$is_ajax && $regenerate_time > 0
			&& (!isset($_SESSION['last_regenerate']) || $_SESSION['last_regenerate'] < (time() - $regenerate_time))) {
			$this->sess_regenerate((bool)($this->config['sess_regenerate_destroy'] ?? false));
		}

		// Sliding cookie expiration
		if (isset($_COOKIE[$this->config['cookie_name']])
			&& hash_equals(session_id(), (string)$_COOKIE[$this->config['cookie_name']])
			&& !headers_sent()) {

			setcookie($this->config['cookie_name'], session_id(), [
				'expires'  => $this->config['cookie_expiration'] ? time() + $this->config['cookie_expiration'] : 0,
				'path'     => $this->config['cookie_path'] ?? '/',
				'domain'   => $this->config['cookie_domain'] ?? '',
				'secure'   => $this->config['cookie_secure'],
				'httponly' => true,
				'samesite' => $this->config['cookie_samesite'] ?? 'Strict'
			]);
		}

		$this->_lava_init_vars();
	}

	/**
	 * Current security status (debug/admin use only - do not expose publicly)
	 *
	 * @return array
	 */
	public function get_security_status()
	{
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		$fingerprint = $this->generate_fingerprint();
		$data = $this->_security_load();

		$key = $ip . '_' . $fingerprint;
		$locked_until = (int)($data[$key]['locked_until'] ?? 0);
		$remaining = max($locked_until - time(), 0);

		$creation_locked_until = (int)($data['creation_' . $ip]['locked_until'] ?? 0);
		$creation_remaining = max($creation_locked_until - time(), 0);

		return [
			'locked'                     => $remaining > 0,
			'locked_until'               => $locked_until,
			'remaining'                  => $remaining,
			'remaining_minutes'          => $remaining > 0 ? (int)ceil($remaining / 60) : 0,
			'attempts'                   => (int)($data[$key]['attempts'] ?? 0),
			'max_attempts'               => $this->max_invalid_attempts,
			'creation_locked'            => $creation_remaining > 0,
			'creation_remaining'         => $creation_remaining,
			'creation_remaining_minutes' => $creation_remaining > 0 ? (int)ceil($creation_remaining / 60) : 0,
			'inactivity_expired'         => $this->inactivity_expired,
			'upload_progress_enabled'    => filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN),
			'ip'                         => $ip
		];
	}

	/**
	 * Validate session id format
	 *
	 * @param string $id
	 * @return bool
	 */
	public function is_valid_sid($id)
	{
		return is_string($id) && preg_match('/^[a-zA-Z0-9,-]{40,128}$/', $id) === 1;
	}

	/**
	 * Key used as a (weak) defense-in-depth check against cookie theft.
	 * It only binds headers an attacker can copy, so it is NOT a substitute
	 * for HTTPS + HttpOnly + XSS prevention. IP is handled by sess_match_ip.
	 *
	 * @return string
	 */
	public function generate_fingerprint()
	{
		$data = [
			$_SERVER['HTTP_USER_AGENT'] ?? '',
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
		];

		return hash_hmac('sha256', implode("\0", $data), $this->hmac_secret);
	}

	/**
	 * Regenerate session ID on login to prevent session fixation
	 *
	 * @param bool $destroy_old
	 */
	public function regenerate_on_login($destroy_old = true)
	{
		$this->sess_regenerate($destroy_old);
		$_SESSION['created_at'] = time();
		$_SESSION['last_activity'] = time();
		$this->reset_attempts();
	}

	/**
	 * Initialize flash vars and populate $this->userdata
	 */
	protected function _lava_init_vars()
	{
		if (!empty($_SESSION['__lava_vars'])) {
			$now = time();
			foreach ($_SESSION['__lava_vars'] as $key => $value) {
				if ($value === 'new') {
					$_SESSION['__lava_vars'][$key] = 'old';
				} elseif ($value === 'old' || (is_int($value) && $value < $now)) {
					unset($_SESSION[$key], $_SESSION['__lava_vars'][$key]);
				}
			}
			if (empty($_SESSION['__lava_vars'])) {
				unset($_SESSION['__lava_vars']);
			}
		}
		$this->userdata = $_SESSION ?? [];
	}

	/**
	 * Get the session ID length
	 *
	 * @return int
	 */
	public function _get_sid_length()
	{
		$bits_per_character = (int) ini_get('session.sid_bits_per_character');
		$sid_length = (int) ini_get('session.sid_length');
		$bits = $sid_length * $bits_per_character;
		if ($bits < 160) {
			$sid_length += (int) ceil((160 - $bits) / $bits_per_character);
		}
		return $sid_length;
	}

	/**
	 * Regenerate session id
	 *
	 * @param bool $destroy
	 */
	public function sess_regenerate(bool $destroy = false)
	{
		if (session_status() !== PHP_SESSION_ACTIVE || headers_sent()) {
			return false;
		}

		$_SESSION['last_regenerate'] = time();

		return session_regenerate_id($destroy);
	}

	/**
	 * Mark a session variable as flashdata
	 *
	 * @param string|array $key
	 * @return bool
	 */
	public function mark_as_flash($key)
	{
		if (is_array($key)) {
			for ($i = 0, $c = count($key); $i < $c; $i++) {
				if (! isset($_SESSION[$key[$i]])) {
					return FALSE;
				}
			}
			$new = array_fill_keys($key, 'new');
			$_SESSION['__lava_vars'] = isset($_SESSION['__lava_vars'])
				? array_merge($_SESSION['__lava_vars'], $new)
				: $new;
			return TRUE;
		}
		if (! isset($_SESSION[$key])) {
			return FALSE;
		}
		$_SESSION['__lava_vars'][$key] = 'new';
		return TRUE;
	}

	/**
	 * Keeps existing flashdata available to next request.
	 *
	 * @param string|array $key
	 */
	public function keep_flashdata($key)
	{
		$this->mark_as_flash($key);
	}

	/**
	 * Native session id
	 */
	public function session_id()
	{
		return session_id();
	}

	/**
	 * Check if session key exists
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function has_userdata($key = null)
	{
		if (! is_null($key)) {
			return isset($_SESSION[$key]);
		}
		return FALSE;
	}

	/**
	 * Set session data
	 *
	 * @param string|array $keys
	 * @param mixed $value
	 */
	public function set_userdata($keys, $value = NULL)
	{
		if (is_array($keys)) {
			foreach ($keys as $key => $val) {
				$_SESSION[$key] = $val;
			}
		} else {
			$_SESSION[$keys] = $value;
		}
	}

	/**
	 * Unset session data
	 *
	 * @param string|array $keys
	 */
	public function unset_userdata($keys)
	{
		foreach ((array) $keys as $key) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * Fetch all flashdata keys
	 *
	 * @return array
	 */
	public function get_flash_keys()
	{
		if (! isset($_SESSION['__lava_vars'])) {
			return array();
		}
		$keys = array();
		foreach ($_SESSION['__lava_vars'] as $key => $val) {
			if (!is_int($val)) $keys[] = $key;
		}
		return $keys;
	}

	/**
	 * Unmark flashdata
	 *
	 * @param string|array $key
	 */
	public function unmark_flash($key)
	{
		if (empty($_SESSION['__lava_vars'])) {
			return;
		}
		is_array($key) OR $key = array($key);
		foreach ($key as $k) {
			if (isset($_SESSION['__lava_vars'][$k]) && ! is_int($_SESSION['__lava_vars'][$k])) {
				unset($_SESSION['__lava_vars'][$k]);
			}
		}
		if (empty($_SESSION['__lava_vars'])) {
			unset($_SESSION['__lava_vars']);
		}
	}

	/**
	 * Fetch session data
	 *
	 * @param string|null $key
	 * @return mixed
	 */
	public function userdata($key = NULL)
	{
		if (isset($key)) {
			return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
		} elseif (empty($_SESSION)) {
			return array();
		}
		$userdata = array();
		$_exclude = array_merge(array('__lava_vars'), $this->get_flash_keys());
		foreach (array_keys($_SESSION) as $k) {
			if (! in_array($k, $_exclude, TRUE)) {
				$userdata[$k] = $_SESSION[$k];
			}
		}
		return $userdata;
	}

	/**
	 * Destroy the current session and expire its cookie
	 */
	public function sess_destroy()
	{
		$_SESSION = [];

		if (session_status() === PHP_SESSION_ACTIVE) {
			if (!headers_sent()) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', [
					'expires'  => time() - 42000,
					'path'     => $params['path'] ?? '/',
					'domain'   => $params['domain'] ?? '',
					'secure'   => $params['secure'] ?? false,
					'httponly' => true,
					'samesite' => $params['samesite'] ?? 'Strict'
				]);
			}
			session_destroy();
		}
	}

	/**
	 * Fetch flashdata
	 *
	 * @param string|null $key
	 * @return mixed
	 */
	public function flashdata($key = NULL)
	{
		if (isset($key)) {
			return (isset($_SESSION['__lava_vars'], $_SESSION['__lava_vars'][$key], $_SESSION[$key]) && ! is_int($_SESSION['__lava_vars'][$key]))
				? $_SESSION[$key]
				: NULL;
		}
		$flashdata = array();
		if (! empty($_SESSION['__lava_vars'])) {
			foreach ($_SESSION['__lava_vars'] as $k => $value) {
				if (!is_int($value) && isset($_SESSION[$k])) $flashdata[$k] = $_SESSION[$k];
			}
		}
		return $flashdata;
	}

	/**
	 * Set flashdata
	 *
	 * @param string|array $data
	 * @param mixed $value
	 */
	public function set_flashdata($data, $value = NULL)
	{
		$this->set_userdata($data, $value);
		$this->mark_as_flash(is_array($data) ? array_keys($data) : $data);
	}

	/**
	 * Initialize session markers
	 * 	
	 * @param string $fingerprint
	 */
	private function _init_session_markers($fingerprint)
	{
		$_SESSION['fingerprint']   = $fingerprint;
		$_SESSION['created_at']    = time();
		$_SESSION['last_activity'] = time();
	}

	/**
	 * Start fresh session
	 * 
	 * @param string $fingerprint
	 */
	private function _start_fresh_session($fingerprint)
	{
		session_id(session_create_id());
		session_start();
		$_SESSION = [];
		$this->_init_session_markers($fingerprint);
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	}

	private function _is_https()
	{
		return (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
	}

	/**
	 * Persistent random secret for non-production environments
	 */
	private function _dev_secret()
	{
		$file = dirname($this->security_file) . '/.dev_hmac_secret';
		$secret = is_file($file) ? trim((string) @file_get_contents($file)) : '';
		if (strlen($secret) < 32) {
			$secret = bin2hex(random_bytes(32));
			$old = umask(0077);
			file_put_contents($file, $secret, LOCK_EX);
			umask($old);
		}
		return $secret;
	}

	/**
	 * Initialize security storage and ensure directory exists.
	 * Keep this directory OUTSIDE the web root (or deny web access to it).
	 */
	private function _security_init()
	{
		$dir = dirname($this->security_file);
		if (!is_dir($dir)) {
			mkdir($dir, 0700, true);
		}

		if (!file_exists($this->security_file)) {
			$old = umask(0077);
			file_put_contents($this->security_file, '{}', LOCK_EX);
			umask($old);
		}
	}

	/**
	 * Read-only load (shared lock)
	 *
	 * @return array
	 */
	private function _security_load()
	{
		$fp = @fopen($this->security_file, 'r');
		if (!$fp) return [];

		if (!flock($fp, LOCK_SH)) {
			fclose($fp);
			return [];
		}

		$json = stream_get_contents($fp);
		flock($fp, LOCK_UN);
		fclose($fp);

		$data = $json ? json_decode($json, true) : [];
		return is_array($data) ? $data : [];
	}

	/**
	 * Atomic read-modify-write under ONE exclusive lock (no lost updates).
	 * Also prunes expired entries so the file cannot grow without bound.
	 *
	 * @param callable $fn function(array &$data, int $now): mixed
	 * @return mixed Whatever $fn returns
	 */
	private function _security_update(callable $fn)
	{
		$fp = @fopen($this->security_file, 'c+');
		if (!$fp) return null;

		if (!flock($fp, LOCK_EX)) {
			fclose($fp);
			return null;
		}

		$json = stream_get_contents($fp);
		$data = $json ? json_decode($json, true) : [];
		if (!is_array($data)) $data = [];

		$now = time();
		$result = $fn($data, $now);

		// Prune dead entries
		$horizon = max($this->invalid_window, $this->creation_window);
		foreach ($data as $k => $entry) {
			$locked = isset($entry['locked_until']) && $entry['locked_until'] > $now;
			$recent = false;
			foreach (['timestamps', 'creations'] as $f) {
				if (!empty($entry[$f]) && max($entry[$f]) > $now - $horizon) $recent = true;
			}
			if (!$locked && !$recent) unset($data[$k]);
		}

		ftruncate($fp, 0);
		rewind($fp);
		fwrite($fp, json_encode($data, JSON_UNESCAPED_SLASHES));
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);

		return $result;
	}

	/**
	 * Check whether the IP/fingerprint is locked (read-only, no disk write)
	 *
	 * @param string $ip
	 * @param string $fingerprint
	 * @return string|null Error message if locked
	 */
	public function security_check_lock($ip, $fingerprint)
	{
		$data = $this->_security_load();
		$now = time();

		foreach ([$ip . '_' . $fingerprint, 'creation_' . $ip] as $key) {
			if (isset($data[$key]['locked_until']) && $now < $data[$key]['locked_until']) {
				$remaining = $data[$key]['locked_until'] - $now;
				return "Access locked. Try again in {$remaining} seconds.";
			}
		}
		return null;
	}

	/**
	 * Log an invalid session attempt (fingerprint/IP mismatch)
	 *
	 * @param string $ip
	 * @param string $fingerprint
	 * @param string $reason
	 */
	public function security_log_attempt($ip, $fingerprint, $reason = 'Unknown')
	{
		$key = $ip . '_' . $fingerprint;
		$window = $this->invalid_window;
		$max = $this->max_invalid_attempts;
		$duration = $this->lock_duration_invalid;

		$locked = $this->_security_update(function (&$data, $now) use ($key, $window, $max, $duration) {
			$data[$key] ??= ['attempts' => 0, 'timestamps' => []];
			$data[$key]['timestamps'][] = $now;
			$data[$key]['timestamps'] = array_values(array_filter(
				$data[$key]['timestamps'],
				fn($t) => $t > $now - $window
			));
			// Attempts are counted inside the window only (previously never decayed)
			$data[$key]['attempts'] = count($data[$key]['timestamps']);

			if ($data[$key]['attempts'] >= $max) {
				$data[$key]['locked_until'] = $now + $duration;
				return true;
			}
			return false;
		});

		if ($locked) {
			error_log("[Session Security] {$ip} locked - {$reason}");
		}
	}

	/**
	 * Track session creation rate per IP (not per fingerprint, so changing the
	 * User-Agent can't be used to dodge the limit).
	 *
	 * @param string $ip
	 * @param string|null $fingerprint Unused, kept for backward compatibility
	 * @return string|null Error message if locked
	 */
	public function security_track_session_creation($ip, $fingerprint = null)
	{
		$key = 'creation_' . $ip;
		$window = $this->creation_window;
		$max = $this->max_session_creations;
		$duration = $this->lock_duration_creation;

		$blocked = $this->_security_update(function (&$data, $now) use ($key, $window, $max, $duration) {
			$data[$key] ??= ['creations' => []];
			$data[$key]['creations'][] = $now;
			$data[$key]['creations'] = array_values(array_filter(
				$data[$key]['creations'],
				fn($t) => $t > $now - $window
			));

			if (count($data[$key]['creations']) > $max) {
				$data[$key]['locked_until'] = $now + $duration;
				return true;
			}
			return false;
		});

		if ($blocked) {
			error_log("[Session Security] IP {$ip} locked for session creation flood.");
			return 'Too many session creations. Please wait before trying again.';
		}
		return null;
	}

	/**
	 * Reset failed attempts for an IP/fingerprint
	 */
	public function reset_attempts($ip = null, $fingerprint = null)
	{
		$ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
		$fingerprint = $fingerprint ?? $this->generate_fingerprint();
		$key = $ip . '_' . $fingerprint;

		$this->_security_update(function (&$data) use ($key) {
			$data[$key] = ['attempts' => 0, 'timestamps' => []];
		});
	}

	/**
	 * Current lock status for an IP/fingerprint
	 *
	 * @return array 'locked', 'attempts', 'max', 'remaining'
	 */
	public function check_lock_status($ip = null, $fingerprint = null)
	{
		$ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
		$fingerprint = $fingerprint ?? $this->generate_fingerprint();
		$key = $ip . '_' . $fingerprint;

		$data = $this->_security_load();
		$locked_until = (int)($data[$key]['locked_until'] ?? 0);
		$remaining = max($locked_until - time(), 0);

		return [
			'locked'    => $remaining > 0,
			'attempts'  => (int)($data[$key]['attempts'] ?? 0),
			'max'       => $this->max_invalid_attempts,
			'remaining' => $remaining
		];
	}

	/**
	 * Manually unlock one user (admin override)
	 *
	 * @return bool True if an entry was found and cleared
	 */
	public function unlock_attempts($ip = null, $fingerprint = null)
	{
		$ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
		$fingerprint = $fingerprint ?? $this->generate_fingerprint();
		$key = $ip . '_' . $fingerprint;

		return (bool) $this->_security_update(function (&$data) use ($key) {
			if (!isset($data[$key])) return false;
			unset($data[$key]['locked_until']);
			$data[$key]['attempts'] = 0;
			$data[$key]['timestamps'] = [];
			return true;
		});
	}

	/**
	 * Unlock everyone (including creation-flood locks)
	 *
	 * @return int Number of records cleared
	 */
	public function unlock_all_attempts()
	{
		return (int) $this->_security_update(function (&$data) {
			$count = 0;
			foreach ($data as $key => $info) {
				if (!is_array($info)) continue;
				if (isset($info['locked_until']) || ($info['attempts'] ?? 0) > 0 || !empty($info['creations'])) {
					unset($data[$key]);
					$count++;
				}
			}
			return $count;
		});
	}

	/**
	 * Regenerate session ID on successful login
	 */
	public function after_successful_login()
	{
		$this->regenerate_on_login(true);
	}

}

?>