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
            if($route_url !== $url && $route_url !== NULL)
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
		$route = route_config();
		$request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
		//hack!
		$script_url  = (isset($_SERVER['PHP_SELF'])) ? substr_replace($_SERVER['PHP_SELF'], '', strpos($_SERVER['PHP_SELF'], 'index.php') + 9) : '';

		if(strpos($request_url, '?') == TRUE)
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
					if (!preg_match('/^['.config_item('permitted_uri_chars').']+$/i', $uri))
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
		$autoload = autoload_config();

		$this->controller = config_item('default_controller');
		$this->method = config_item('default_method');

		$segments = $this->parseUrl();
		
		if(isset($segments[0]) && !empty($segments[0]))
			$this->controller = ucfirst($segments[0]);
		if(isset($segments[1]) && !empty($segments[1]))
			$this->method = str_replace('-', '_', $segments[1]);

		if(file_exists(APP_DIR . 'controllers/' . $this->controller . '.php'))
		{
			require(APP_DIR . 'controllers/' . $this->controller . '.php');
			unset($segments[0]);

			if(method_exists($this->controller, $this->method))
				unset($segments[1]);
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