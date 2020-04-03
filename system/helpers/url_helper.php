<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| LAVALust - a lightweight PHP MVC Framework is free software:
| ------------------------------------------------------------------- 
| you can redistribute it and/or modify it under the terms of the
| GNU General Public License as published
| by the Free Software Foundation, either version 3 of the License,
| or (at your option) any later version.
|
| LAVALust - a lightweight PHP MVC Framework is distributed in the hope
| that it will be useful, but WITHOUT ANY WARRANTY;
| without even the implied warranty of
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
| GNU General Public License for more details.
|
| You should have received a copy of the GNU General Public License
| along with LAVALust - a lightweight PHP MVC Framework.
| If not, see <https://www.gnu.org/licenses/>.
|
| @author     Ronald M. Marasigan
| @copyright  Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license    https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link   https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/
if ( ! function_exists('redirect'))
{
	/*
	* ------------------------------------------------------
	*  Redirect to a location
	* ------------------------------------------------------
	*/
	function redirect($loc)
	{
		header('Location: '. BASE_URL . language_url($loc));
	}
}

if ( ! function_exists('load_js'))
{
	/*
	* ------------------------------------------------------
	*  Pre-loaded JS
	* ------------------------------------------------------
	*/
	function load_js($paths)
	{
		foreach ($paths as $path) {
			echo '<script src="' . BASE_URL . 'assets/js/' . $path . '.js"></script>' . "\r\n";
		}
	}
}

if ( ! function_exists('load_css'))
{
	/*
	* ------------------------------------------------------
	*  Pre-loaded CSS
	* ------------------------------------------------------
	*/
	function load_css($paths)
	{
		foreach ($paths as $path) {
			echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/' . $path . '.css" type="text/css" />' . "\r\n";
		}
	}
}

if ( ! function_exists('site_url'))
{
	/*
	* ------------------------------------------------------
	*  Site URL
	* ------------------------------------------------------
	*/
	function site_url($url) {
		if(isset($_COOKIE['language'])) {
			return $_COOKIE['language'] . '/' . $url;
		} else {
			return $url;
		}
	}
}

if ( ! function_exists('active'))
{
	/*
	* ------------------------------------------------------
	*  Active class for URL
	* ------------------------------------------------------
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
	/*
	* ------------------------------------------------------
	*  Segment
	* ------------------------------------------------------
	*/
	function segment($seg)
	{
		if(!is_int($seg)) return false;
		
		$parts = explode('/', $_SERVER['REQUEST_URI']);
	    return isset($parts[$seg]) ? $parts[$seg] : false;
	}
}

?>