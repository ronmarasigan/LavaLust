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
*  Class Security
* ------------------------------------------------------
*/
class Security
{
	/**
	 * Random Hash for Cross Site Request Forgery Protection Cookie
	 *
	 * @var string
	 * @access protected
	 */
	protected $_csrf_hash			= '';
	/**
	 * Expiration time for Cross Site Request Forgery Protection Cookie
	 * Defaults to two hours (in seconds)
	 *
	 * @var int
	 * @access protected
	 */
	protected $_csrf_expire			= 7200;
	/**
	 * Token name for Cross Site Request Forgery Protection Cookie
	 *
	 * @var string
	 * @access protected
	 */
	protected $_csrf_token_name		= 'ci_csrf_token';
	/**
	 * Cookie name for Cross Site Request Forgery Protection Cookie
	 *
	 * @var string
	 * @access protected
	 */
	protected $_csrf_cookie_name	= 'ci_csrf_token';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// CSRF config
		foreach(array('csrf_expire', 'csrf_token_name', 'csrf_cookie_name') as $key)
		{
			if (FALSE !== ($val = config_item($key)))
			{
				$this->{'_'.$key} = $val;
			}
		}

		// Append application specific cookie prefix
		if (config_item('cookie_prefix'))
		{
			$this->_csrf_cookie_name = config_item('cookie_prefix').$this->_csrf_cookie_name;
		}

		// Set the CSRF hash
		$this->_csrf_set_hash();
	}

	/**
	 * Verify Cross Site Request Forgery Protection
	 *
	 * @return	object
	 */
	public function csrf_verify()
	{
		// If no POST data exists we will set the CSRF cookie
		if (count($_POST) == 0)
		{
			return $this->csrf_set_cookie();
		}

		// Do the tokens exist in both the _POST and _COOKIE arrays?
		if ( ! isset($_POST[$this->_csrf_token_name]) OR
			 ! isset($_COOKIE[$this->_csrf_cookie_name]))
		{
			$this->csrf_show_error();
		}

		// Do the tokens match?
		if ($_POST[$this->_csrf_token_name] != $_COOKIE[$this->_csrf_cookie_name])
		{
			$this->csrf_show_error();
		}

		// We kill this since we're done and we don't want to
		// polute the _POST array
		unset($_POST[$this->_csrf_token_name]);

		// Nothing should last forever
		unset($_COOKIE[$this->_csrf_cookie_name]);
		$this->_csrf_set_hash();
		$this->csrf_set_cookie();

		return $this;
	}

	/**
	 * Set Cross Site Request Forgery Protection Cookie
	 *
	 * @return	object
	 */
	public function csrf_set_cookie()
	{
		$expire = time() + $this->_csrf_expire;
		$secure_cookie = (config_item('cookie_secure') === TRUE) ? 1 : 0;

		if ($secure_cookie)
		{
			$req = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : FALSE;

			if ( ! $req OR $req == 'off')
			{
				return FALSE;
			}
		}

		setcookie($this->_csrf_cookie_name, $this->_csrf_hash, $expire, config_item('cookie_path'), config_item('cookie_domain'), $secure_cookie);

		return $this;
	}

	/**
	 * Show CSRF Error
	 *
	 * @return	void
	 */
	public function csrf_show_error()
	{
		show_error('Not Allowed Error', 'The action you have requested is not allowed.', 404);
	}

	/*
	* ------------------------------------------------------
	*  Escaping characters for a simple XSS Filter
	* ------------------------------------------------------
	*/
	public function xss_clean($string)
	{
		$escaper =& load_class('Escaper', 'libraries');
		return $escaper->filter($string);
	}

	/**
	 * Get CSRF Hash
	 *
	 * Getter Method
	 *
	 * @return 	string 	self::_csrf_hash
	 */
	public function get_csrf_hash()
	{
		return $this->_csrf_hash;
	}

	/**
	 * Get CSRF Token Name
	 *
	 * Getter Method
	 *
	 * @return 	string 	self::csrf_token_name
	 */
	public function get_csrf_token_name()
	{
		return $this->_csrf_token_name;
	}

	/**
	 * Set Cross Site Request Forgery Protection Cookie
	 *
	 * @return	string
	 */
	protected function _csrf_set_hash()
	{
		if ($this->_csrf_hash == '')
		{
			// If the cookie exists we will use it's value.
			// We don't necessarily want to regenerate it with
			// each page load since a page could contain embedded
			// sub-pages causing this feature to fail
			if (isset($_COOKIE[$this->_csrf_cookie_name]) &&
				$_COOKIE[$this->_csrf_cookie_name] != '')
			{
				return $this->_csrf_hash = $_COOKIE[$this->_csrf_cookie_name];
			}

			return $this->_csrf_hash = md5(uniqid(rand(), TRUE));
		}

		return $this->_csrf_hash;
	}

}

?>