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
 * @version Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
* ------------------------------------------------------
*  Class Input
* ------------------------------------------------------
*/
Class Io {

	private $_enable_csrf			= FALSE;

	private $security;

	public function __construct()
	{
		$this->security =& load_class('Security', 'core');
		$this->_enable_csrf	= (config_item('csrf_protection') === TRUE);

		// CSRF Protection check
		if ($this->_enable_csrf === TRUE)
		{
			$this->security->csrf_verify();
		}
	}
	
  	/**
  	 * POST Variable
  	 * @param  string
  	 * @return string
  	 */
	public function post($index = NULL)
	{
		if($index === NULL && !empty($_POST)) {
			$post = array();
			foreach($_POST as $key => $value) {
				$post[$key] = $value;
			}
			return $post;
		}
		return $_POST[$index];
	}

	/**
  	 * GET Variable
  	 * @param  string
  	 * @return string
  	 */
	public function get($index = NULL)
	{
		if($index === NULL && !empty($_GET)) {
			$get = array();
			foreach($_GET as $key => $value) {
				$get[$key] = $value;
			}
			return $get;
		}
		return $_GET[$index];
	}

	/**
  	 * COOKIE Variable
  	 * @param  string
  	 * @return string
  	 */
	public function cookie($index = NULL)
	{
		if($index === NULL && !empty($_COOKIE)) {
			$cookie = array();
			foreach($_COOKIE as $key => $value) {
				$cookie[$key] = $value;
			}
			return $cookie;
		}
		return $_COOKIE[$index];
	}

	/**
	 * Set cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param	string|mixed[]	$name		Cookie name or an array containing parameters
	 * @param	string		$value		Cookie value
	 * @param	int		$expire		Cookie expiration time in seconds
	 * @param	string		$domain		Cookie domain (e.g.: '.yourdomain.com')
	 * @param	string		$path		Cookie path (default: '/')
	 * @param	string		$prefix		Cookie name prefix
	 * @param	bool		$secure		Whether to only transfer cookies via SSL
	 * @param	bool		$httponly	Whether to only makes the cookie accessible via HTTP (no javascript)
	 * @return	void
	 */
	public function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL)
	{
		if (is_array($name))
		{
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
			{
				if (isset($name[$item]))
				{
				$$item = $name[$item];
				}
			}
		}

	if ($prefix === '' && config_item('cookie_prefix') !== '')
	{
		$prefix = config_item('cookie_prefix');
	}

	if ($domain == '' && config_item('cookie_domain') != '')
	{
		$domain = config_item('cookie_domain');
	}

	if ($path === '/' && config_item('cookie_path') !== '/')
	{
		$path = config_item('cookie_path');
	}

	$secure = ($secure === NULL && config_item('cookie_secure') !== NULL)
	? (bool) config_item('cookie_secure')
	: (bool) $secure;

	$httponly = ($httponly === NULL && config_item('cookie_httponly') !== NULL)
	? (bool) config_item('cookie_httponly')
	: (bool) $httponly;

	if ( ! is_numeric($expire))
	{
		$expire = time() - 86500;
	}
	else
	{
		$expire = ($expire > 0) ? time() + $expire : 0;
	}

	setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	public function is_ajax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}
}
	
?>
