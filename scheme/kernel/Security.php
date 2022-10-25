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
 * @copyright Copyright 2020 (https://ronmarasigan.github.io)
 * @since Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * Class Security
 */
class Security
{
	/**
	 * hash hmac string
	 *
	 * @var string
	 */
	private $_csrf_hmac_string = 	'HIOtrs19yBm76xDz710LkNmFAbL';

	/**
	 * CSRF hash
	 *
	 * @var [type]
	 */
	protected $_csrf_hash;

	/**
	 * CSRF token name
	 *
	 * @var string
	 */
	protected $_csrf_token_name = 	'lava_csrf_token';

	/**
	 * CSRF cookie name
	 *
	 * @var string
	 */
	protected $_csrf_cookie_name =	'lava_csrf_token';

	/**
	 * CSRF token expire
	 *
	 * @var integer
	 */
	protected $_csrf_expire = 7200;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		//check if csrf protection was enabled
		if (config_item('csrf_protection'))
		{
			foreach (array('csrf_expire', 'csrf_token_name', 'csrf_cookie_name') as $key)
			{
				$this->{'_'.$key} = config_item($key);
			}

			$this->_csrf_cookie_name = ! empty(config_item('cookie_prefix')) ? config_item('cookie_prefix').$this->_csrf_cookie_name : $this->_csrf_cookie_name;

			//set Hmac Hash
			$this->_csrf_set_hash();
		}
	}

	/**
	 * Set hash_hmac
	 *
	 * @param string $token
	 * @return void
	 */
	public function _hash_hmac($token)
	{
		return hash_hmac('SHA256', $this->_csrf_hmac_string, $token);
	}

	/**
	 * Setting up hash_hmac token
	 *
	 * @return void
	 */
	public function _csrf_set_hash()
    {
		if(isset($_COOKIE[$this->_csrf_cookie_name]))
		{
			return $this->_csrf_hash = $_COOKIE[$this->_csrf_cookie_name];
		}
		$this->_csrf_hash = $this->_hash_hmac(bin2hex(random_bytes(32)));

		return $this->_csrf_hash;
    }

	/**
	 * CSRF Validate
	 *
	 * @return void
	 */
	public function csrf_validate()
	{
		// If it's not a POST request we will just ignore it
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
		{
			return $this->csrf_set_cookie();
		}

		$uri = filter_var(ltrim($_SERVER['REQUEST_URI'], '/'), FILTER_SANITIZE_URL) ?? '';

		if(count(config_item('csrf_exclude_uris')) > 0)
		{
			foreach(config_item('csrf_exclude_uris') as $excluded)
			{
				if (! preg_match('#^'.$excluded.'$#i',  $uri))
				{
					$is_valid = isset($_POST[$this->_csrf_token_name], $_COOKIE[$this->_csrf_cookie_name])
					&& is_string($_POST[$this->_csrf_token_name]) && is_string($_COOKIE[$this->_csrf_cookie_name])
					&& hash_equals($_POST[$this->_csrf_token_name], $_COOKIE[$this->_csrf_cookie_name]);

					unset($_POST[$this->_csrf_token_name]);

					if (config_item('csrf_regenerate'))
					{
						unset($_COOKIE[$this->_csrf_cookie_name]);
						$this->_csrf_hash = NULL;
					}

					$this->_csrf_set_hash();
					$this->csrf_set_cookie();

					if ($is_valid === FALSE)
					{
						show_error('403 Forbidden Error', 'The action you have requested is not allowed.', 'error_general', 403);
					}

					return $this;
				}
			}
		}
	}

	/**
	 * Setting up CSRF cookie
	 *
	 * @return void
	 */
	public function csrf_set_cookie()
	{
		$expiration = time() + $this->_csrf_expire;

		setcookie($this->_csrf_cookie_name,
			$this->_csrf_hash,
			array('samesite' => 'Strict',
			'secure'   => FALSE,
			'expires'  => $expiration,
			'path'     => config_item('cookie_path'),
			'domain'   => config_item('cookie_domain'),
			'httponly' => config_item('cookie_httponly'))
		);
		
		return $this;
	}

	/**
	 * Get csrf hash
	 *
	 * @return void
	 */
	public function get_csrf_hash()
	{
		return $this->_csrf_hash;
	}

	/**
	 * Set  CSRF hash
	 *
	 * @return void
	 */
	public function get_csrf_token_name()
	{
		return $this->_csrf_token_name;
	}

	/**
	 * Sanitize for a file system
	 * 
	 * @param  string $name
	 * @return string
	 */
	public function sanitize_filename($name) {
	    // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
	    $name = str_replace(array_merge(
	        array_map('chr', range(0, 31)),
	        array('<', '>', ':', '"', '/', '\\', '|', '?', '*')
	    ), '', $name);
	    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
	    $ext = pathinfo($name, PATHINFO_EXTENSION);
	    $name= mb_strcut(pathinfo($name, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($name)) . ($ext ? '.' . $ext : '');
	    return $name;
	}
}

?>