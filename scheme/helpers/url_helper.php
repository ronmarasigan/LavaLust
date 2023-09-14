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
		$base_url = filter_var(BASE_URL, FILTER_SANITIZE_URL);
		return ! empty(config_item('index_page')) ? $base_url . config_item('index_page').'/' . $url : $base_url . $url;
	}
}

if ( ! function_exists('base_url'))
{
	/**
	 * Base URL
	 *
	 * @return void
	 */
	function base_url() 
	{
		return  filter_var(BASE_URL, FILTER_SANITIZE_URL);
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
	function active($currect_page, $css_class = 'active')
	{
		// Explode REQUEST_URI
		$uri_array =  explode('/', rtrim(strtok($_SERVER["REQUEST_URI"], '?'), '/'));
		// Explode the BASE_URL
		$url_array = explode('/', trim(preg_replace('(^https?://)', '', BASE_URL), '/'));
		// Find the installation folder index base on $url_array
		$folder_index = array_search(end($url_array), array_values($uri_array));
		// Check if index_page is not empty in config file
		if(! empty(config_item('index_page')))
		{
			// +2 to the installation folder index to get the index of the route and the rest of the segments if index_page is not empty
			$url = implode('/', array_slice($uri_array, $folder_index + 2));
		}
		else
		{
			// +1 to the installation folder index to get the index of route and the rest of the segments if index_page is not empty
			$url = implode('/', array_slice($uri_array, $folder_index + 1));
		}
		
		if($currect_page == explode('/', $url)[0] || $currect_page == $url)
		{
			echo $css_class;
		}
	}
}

if ( ! function_exists('segment'))
{
	/**
	 * URI Segment
	 * 
	 * @param  int $seg	URI Segment
	 * @return int
	 */
	function segment($seg)
	{
		$parts = is_int($seg) ? explode('/', $_SERVER['REQUEST_URI']) : FALSE;
	    return isset($parts[$seg]) ? $parts[$seg] : false;
	}
}

?>