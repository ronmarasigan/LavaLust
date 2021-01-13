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

if ( ! function_exists('redirect'))
{
	/**
	 * Redirect to a location
	 * @param  string  $uri    Redirect URL
	 * @param  string  $method Refresh or Location
	 * @param  integer $sec    [description]
	 * @return mixed
	 */
	function redirect($uri, $method = NULL, $sec = 0)
	{
		switch ($method)
		{
			case 'refresh':
				header('Refresh:' .$sec. ';url='. site_url($uri).'');
				break;
			default:
				header('Location: '. site_url($uri), TRUE);
				break;
		}
	}
}

if ( ! function_exists('load_js'))
{
	/**
	 * Pre-loaded JS
	 * @param  string $paths URL path
	 * @return mixed
	 */
	function load_js($paths)
	{
		foreach ($paths as $path) {
			echo '<script src="' . BASE_URL . PUBLIC_DIR . '/' . $path . '.js"></script>' . "\r\n";
		}
	}
}

if ( ! function_exists('load_css'))
{
	/**
	 * Pre-loaded CSS
	 * @param  string $paths URL path
	 * @return mixed
	 */
	function load_css($paths)
	{
		foreach ($paths as $path) {
			echo '<link rel="stylesheet" href="' . BASE_URL . PUBLIC_DIR .'/' . $path . '.css" type="text/css" />' . "\r\n";
		}
	}
}

if ( ! function_exists('site_url'))
{
	/**
	 * Site URL
	 * @param  string $url
	 * @return string
	 */
	function site_url($url='') {
		return BASE_URL . $url;
	}
}

if ( ! function_exists('active'))
{
	/*
	* ------------------------------------------------------
	*  Active class for URL
	* ------------------------------------------------------
	*/
	/**
	 * Active class for URL
	 * @param  string $currect_page
	 * @return string
	 */
	function active($currect_page){
	$url_array =  explode('/', $_SERVER['REQUEST_URI']) ;
		if(count($url_array) > 1)
			$url = $url_array[1];
		else
			$url = $url_array[0];
		
		if($currect_page == $url){
				echo 'active';
		}
	}
}

if ( ! function_exists('segment'))
{
	/**
	 * URI Segment
	 * @param  string $seg URI Segment
	 * @return int      Integer Part
	 */
	function segment($seg)
	{
		if(!is_int($seg)) return false;
		
		$parts = explode('/', $_SERVER['REQUEST_URI']);
	    return isset($parts[$seg]) ? $parts[$seg] : false;
	}
}

?>