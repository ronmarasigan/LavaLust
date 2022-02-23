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

if ( ! function_exists('redirect'))
{
	/**
	 * Handle redirection function using header()
	 *
	 * @param string $uri
	 * @param boolean $permanent
	 * @return void
	 */
	function redirect($uri = '', $permanent = false)
	{
		if ( ! preg_match('#^(\w+:)?//#i', $uri))
		{
			$uri = site_url($uri);
		}
		if (headers_sent() === false)
		{
			header('Location: ' . $uri, true, ($permanent === true) ? 301 : 302);
		}
		exit();
	}
}

if ( ! function_exists('load_js'))
{
	/**
	 * Use to load of Javascript
	 *
	 * @param array $paths
	 * @return void
	 */
	function load_js($paths)
	{
		foreach ($paths as $path)
		{
			echo '<script src="' . BASE_URL . PUBLIC_DIR . '/' . $path . '.js"></script>' . "\r\n";
		}
	}
}

if ( ! function_exists('load_css'))
{
	/**
	 * Use to load of Javascript
	 *
	 * @param array $paths
	 * @return void
	 */
	function load_css($paths)
	{
		foreach ($paths as $path)
		{
			echo '<link rel="stylesheet" href="' . BASE_URL . PUBLIC_DIR .'/' . $path . '.css" type="text/css" />' . "\r\n";
		}
	}
}

if ( ! function_exists('site_url'))
{
	/**
	 * Get the site url
	 *
	 * @param string $url
	 * @return void
	 */
	function site_url($url = '') 
	{
		return ! empty(config_item('index_page')) ? BASE_URL . config_item('index_page').'/' . $url : BASE_URL . $url;
	}
}

if ( ! function_exists('active'))
{
	/**
	 * Active class for URL
	 *
	 * @param string $currect_page
	 * @return void
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
	 * 
	 * @param  int $seg	URI Segment
	 * @return int      	Integer Part
	 */
	function segment($seg)
	{
		if(! is_int($seg)) return false;
		
		$parts = explode('/', $_SERVER['REQUEST_URI']);
	    return isset($parts[$seg]) ? $parts[$seg] : false;
	}
}

?>