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

/**
 * Class Router
 */
class Router
{
	/**
	 * url from URI
	 *
	 * @var array
	 */
	protected $url = array();

	/**
	 * url string
	 *
	 * @var string
	 */
	protected $uri_string = '';

	/**
	 * Controller
	 *
	 * @var string
	 */
	protected $controller;

	/**
	 * $Method
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Parameters
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Routes
	 *
	 * @var array
	 */
	private $route = array();

	/**
	 * Class Constructor
	 */
	public function __construct() {

		//Routes
		$this->route =& route_config();
	}

	/**
	 * Re-Routing
	 *
	 * @param  string $url
	 * @param  array $route
	 * @return string
	 */
	public function remap_url($url, $route)
    {
		$http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : '';

        foreach($route as $pattern => $replacement)
        {
			$replacement = $replacement;
			if (is_array($replacement))
			{
				$replacement = array_change_key_case($replacement, CASE_LOWER);

				if (isset($replacement[$http_verb]))
				{
					$replacement = $replacement[$http_verb];
				} else {
					continue;
				}
			}

            $pattern = str_replace(':any', '(.+)', $pattern);
            $pattern = str_replace(':num', '(\d+)', $pattern);
            $pattern = '/' . str_replace('/', '\/', $pattern) . '/i';
            $route_url = preg_replace($pattern, $replacement, $url);
            if($route_url !== $url && $route_url !== NULL)
				return $route_url;
        }
        return $this->uri_string;
    }

    /**
     * URL Parsing using $_SERVER['SCRIPT_NAME']
     *
     * @return string
     */
	public function parse_url()
	{
		//Get the URI String
		$this->uri_string = trim(str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']), '/');

		//Get the segments
		$this->url = explode('/', $this->remap_url(filter_var($this->uri_string, FILTER_SANITIZE_URL), $this->route));
		
		if($this->url[0]) {
			foreach($this->url as $uri)
			{
				if (! preg_match('/^['.config_item('permitted_uri_chars').']+$/i', $uri))
				{
					show_error('400 Bad Request', 'The URI you submitted has disallowed characters.', 'error_general', 400);
				}
			}
		}

		return $this->url;
	}

	/**
	 * Initiate Routing
	 *
	 * @return $this
	 */
	public function initiate()
	{
		/**
		 * Default Controller
		 *
		 * @var string
		 */
		$this->controller = config_item('default_controller');

		/**
		 * Default Method
		 *
		 * @var string
		 */
		$this->method = config_item('default_method');

		/**
		 * Segments
		 *
		 * @var array
		 */
		$segments = $this->parse_url();

		/**
		 * Get the Controller Segment
		 */
		if(isset($segments[0]) && !empty($segments[0]))
		{
			if($this->route['translate_uri_dashes'] == TRUE)
				$this->controller = str_replace('-', '_', ucfirst($segments[0]));
			else
				$this->controller = ucfirst($segments[0]);
		}

		/**
		 * Get The Method Segment
		 */
		if(isset($segments[1]) && !empty($segments[1]))
		{
			if($this->route['translate_uri_dashes'] == TRUE)
				$this->method = str_replace('-', '_', $segments[1]);
			else
				$this->method = $segments[1];
		}

		/**
		 * Checking if controller exist
		 */
		if(file_exists(APP_DIR . 'controllers/' . $this->controller . '.php'))
		{
			require(APP_DIR . 'controllers/' . $this->controller . '.php');
			unset($segments[0]);

			/**
			 * Checking if method exist
			 */
			if(method_exists($this->controller, $this->method))
				unset($segments[1]);
			else
				empty($this->route['404_override']) ? show_404() : show_404('', '', $this->route['404_override']);

			/**
			 * Check if there are parameters in the URI
			 *
			 * @var array
			 */
			$this->params = $segments ? array_values($segments) : array();

			/**
			 * Load the controller, method and parameters
			 */
			call_user_func_array([new $this->controller, $this->method], $this->params);

		} else {
			empty($this->route['404_override']) ? show_404() : show_404('', '', $this->route['404_override']);
		}
	}
}

?>