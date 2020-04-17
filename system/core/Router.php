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
*  Class Router
* ------------------------------------------------------
*/
class Router
{
	protected $url = array();
	protected $url_string = '';
	protected $controller;
	protected $method;
	protected $params = array();

	/*
	 * ------------------------------------------------------
	 *  Function for re-routing
	 * ------------------------------------------------------
	 */
	public function remapUrl($url, $route)
    {
        foreach($route as $pattern => $replacement)
        {
            $pattern = str_replace(":any", "(.+)", $pattern);
            $pattern = str_replace(":num", "(\d+)", $pattern);
            $pattern = '/' . str_replace("/", "\/", $pattern) . '/i';
            $route_url = preg_replace($pattern, $replacement, $url);
            if($route_url !== $url && $route_url !== null)
				return $route_url;
        }
        return $this->url_string;
    }

    /*
	 * ------------------------------------------------------
	 *  Function for URL Parsing using $_SERVER['REQUEST_URI']
	 * ------------------------------------------------------
	 */
	public function parseUrl()
	{
		$config = get_config();
		$route = route_config();
		$request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
		$script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';

		if(strpos($request_url, '?') == true)
		{
			$exp = explode('?', $request_url);
			$request_url = $exp[0];
		}

		if($request_url != $script_url)
			$this->url_string = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
			$this->url = explode('/', $this->remapUrl(filter_var($this->url_string, FILTER_SANITIZE_URL), $route));
			if($this->url[0] != NULL)
			{
				foreach($this->url as $uri)
				{
					if (!preg_match('/^['.$config['permitted_uri_chars'].']+$/i', $uri))
						trigger_error("The characters you entered in the URL are not permitted!", E_USER_WARNING);
				}
			}
			return $this->url;				
	}

	/*
	 * ------------------------------------------------------
	 *  Initiate Routing
	 * ------------------------------------------------------
	 */
	public function initiate()
	{
		$config = get_config(); 
		$autoload = autoload_config();

		$this->controller = $config['default_controller'];
		$this->method = $config['default_method'];

		$segments = $this->parseUrl();

		/***** Set default indexes *****/
		$controller_index = 0;
		$method_index = 1;
		$script_index = 0;

		/**** This is optional. If you will deploy this on your server
		such like https://mywebsite.com
		You can remove this condition *****/

		if($_SERVER['SERVER_NAME']=='localhost' || $_SERVER['SERVER_NAME']=='127.0.0.1')
		{
			$controller_index = 1;
			$method_index = 2;
			if(count($segments) > 1)
				$script_index = 1;
		}
		
		/*
		 * ------------------------------------------------------
		 *  Function for Available Language
		 * ------------------------------------------------------
		 */
		if(in_array($segments[$script_index], $autoload['language'])) {
			if(file_exists(SYSTEM_DIR . 'language/' . strtolower($segments[$controller_index]) . '_lang.php'))
				require(SYSTEM_DIR . 'language/' . strtolower($segments[$controller_index]) . '_lang.php');
			else
				require(APP_DIR . 'language/' . strtolower($segments[$controller_index]) . '_lang.php');

			setcookie('language', strtolower($segments[$controller_index]), time() + (60*60*24*30));
			unset($segments[$controller_index]);

			/*
			 * ------------------------------------------------------
			 *  Function for setting segment if language is available
			 * ------------------------------------------------------
			 */
			$controller_index += 1;
			$method_index += 1;
		}
		else
			require(SYSTEM_DIR . 'language/' . strtolower($config['language']) . '_lang.php');

		if(isset($segments[$controller_index]) && !empty($segments[$controller_index]))
			$this->controller = $segments[$controller_index];
		if(isset($segments[$method_index]) && !empty($segments[$method_index]))
			$this->method = str_replace('-', '_', $segments[$method_index]);

		if(file_exists(APP_DIR . 'controllers/' . $this->controller . '.php'))
		{
			require(APP_DIR . 'controllers/' . $this->controller . '.php');
			if($controller_index != 0)
				unset($segments[$controller_index], $segments[0]);
			else
				unset($segments[0]);

			if(method_exists($this->controller, $this->method))
				unset($segments[$method_index]);
			else
				/*
				 * ------------------------------------------------------
				 *  You can set this to default method if you like
				 * ------------------------------------------------------
				 */
				show_404('404 Page Not Found', 'The requested page does not found.');

			$this->params = $segments ? array_values($segments) : [];
				call_user_func_array([new $this->controller, $this->method], $this->params);

		} else {
			/*
			 * ------------------------------------------------------
			 *  You can set this to default conroller if you like
			 * ------------------------------------------------------
			 */
			show_404('404 Page Not Found', 'The requested page does not found.');
		}	
	}
}

?>