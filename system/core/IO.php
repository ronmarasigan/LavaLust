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
 * @copyright Copyright 2020 (https://techron.info)
 * @version Version 1.2
 * @link https://lavalust.com
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
* ------------------------------------------------------
*  Class Input
* ------------------------------------------------------
*/
Class Io {

	private $security;
    
	public function __construct()
  	{
    	global $Security;
    	$this->security = $Security;
  	}
  	/**
  	 * POST Variable
  	 * @param  string
  	 * @param  boolean $xss_clean Use this to sanitize you output. (for XSS prevention)
  	 * @return string
  	 */
	public function post($post = NULL, $xss_clean = FALSE)
	{
		if ( isset($_POST[$post]) ) {
			if ( $xss_clean === TRUE ) {
				return $this->security->xss_clean($_POST[$post]);		
			} else {
				return $_POST[$post];	
			}
		}
	}

	/**
  	 * GET Variable
  	 * @param  string
  	 * @param  boolean $xss_clean Use this to sanitize you output. (for XSS prevention)
  	 * @return string
  	 */
	public function get($get = NULL, $xss_clean = FALSE)
	{
		if ( isset($_GET[$get]) ) {
			if ( $xss_clean === TRUE ) {
				return $this->security->xss_clean($_GET[$get]);
			} else {
				return $_GET[$get];
			}
		}
	}

	/**
  	 * COOKIE Variable
  	 * @param  string
  	 * @param  boolean $xss_clean Use this to sanitize you output. (for XSS prevention)
  	 * @return string
  	 */
	public function cookie($cookie = NULL, $xss_clean = FALSE)
	{
		if ( isset($_COOKIE[$cookie]) ) {
			if ( $xss_clean === TRUE ) {
				return $this->security->xss_clean($_COOKIE[$cookie]);		
			} else {
				return $_COOKIE[$cookie];	
			}
		}
	}

	/*
	* ------------------------------------------------------
	*  Setting Up Cookie Cookies
	* ------------------------------------------------------
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
}
	
?>