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
 * @version Version 1.3.4
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

if ( ! function_exists('set_cookie'))
{
	/**
	 * [set_cookie description]
	 * @param mixed $name
	 * @param string $value    the value of the cookie
	 * @param string $expire   the number of seconds until expiration
	 * @param string $domain   the cookie domain.  Usually:  .yourdomain.com
	 * @param string $path     the cookie path
	 * @param string $prefix   the cookie prefix
	 * @param [type] $secure   true makes the cookie secure
	 * @param [type] true makes the cookie accessible via http(s) only (no javascript)
	 * @return void
	 */
	function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL)
	{
		// Set the config file options
		get_instance()->io->set_cookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httponly);
	}
}

if ( ! function_exists('get_cookie'))
{
	/**
	 * Fetch an item from the COOKIE array
	 * @param  string  $index
	 * @param  boolean $xss_clean
	 * @return mixed
	 */
	function get_cookie($index, $xss_clean = FALSE)
	{
		$prefix = isset($_COOKIE[$index]) ? '' : config_item('cookie_prefix');
		return get_instance()->input->cookie($prefix.$index, $xss_clean);
	}
}

if ( ! function_exists('delete_cookie'))
{
	/*
	* ------------------------------------------------------
	*  Delete cookie
	* ------------------------------------------------------
	*/
	/**
	 * Delete a cookie
	 * @param  [type] $name   [description]
	 * @param  string $domain the cookie domain. Usually: .yourdomain.com
	 * @param  string $path   the cookie path
	 * @param  string $prefix the cookie prefix
	 * @return void
	 */
	function delete_cookie($name, $domain = '', $path = '/', $prefix = '')
	{
		set_cookie($name, '', '', $domain, $path, $prefix);
	}
}