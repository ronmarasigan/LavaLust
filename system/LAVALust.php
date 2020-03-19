<?php
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
| @author 		Ronald M. Marasigan
| @copyright	Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license		https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link		https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/

class LAVALust {

	protected $url = array();
	protected $url_string = '';
	protected $controller;
	protected $method;
	protected $params = array();

	function __construct()
	{
		require SYSTEM_DIR . 'core/Common.php';

		/***** Deployment Environment *****/
		global $config;
		switch ($config['ENV'])
		{
			case 'development':
				$this->error_handlers();
				error_reporting(-1);
				ini_set('display_errors', 1);	
			break;

			case 'testing':
			case 'production':
				ini_set('display_errors', 0);
				error_reporting(0);
			break;

			default :
				$this->error_handlers();
				error_reporting(-1);
				ini_set('display_errors', 1);
		}
	}

	public function autoload()
	{
		spl_autoload_register(function ($class)
		{
			$class = strtolower($class);
			if (file_exists(ROOT_DIR . 'system/core/' . ucfirst(strtolower($class)) . '.php'))
				require ROOT_DIR . 'system/core/' . ucfirst(strtolower($class)) . '.php';
		});
	}

	public function error_handlers()
	{
		set_error_handler('errors');
		set_exception_handler('exceptions');
		register_shutdown_function('shutdown');
	}
	
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

	public function parseUrl()
	{
		global $config, $route;
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

	public function initiate()
	{
		global $config, $autoload;

		$this->controller = $config['default_controller'];
		$this->method = $config['default_method'];

		$segments = $this->parseUrl();

		/***** Set default indexes *****/
		$controller_index = 0;
		$method_index = 1;
		$script_index = 0;
		
		/***** Language Setting *****/
		if(in_array($segments[$script_index], $autoload['language'])) {
			if(file_exists(SYSTEM_DIR . 'language/' . strtolower($segments[$controller_index]) . '_lang.php'))
				require(SYSTEM_DIR . 'language/' . strtolower($segments[$controller_index]) . '_lang.php');
			else
				require(APP_DIR . 'language/' . strtolower($segments[$controller_index]) . '_lang.php');

			setcookie('language', strtolower($segments[$controller_index]), time() + (60*60*24*30));
			unset($segments[$controller_index]);
			/***** set the segment of controller and action if language is available *****/
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
				/***** You can set this to default method if you like *****/
				show_404('404 Page Not Found', 'The requested page does not found.');

			$this->params = $segments ? array_values($segments) : [];
				call_user_func_array([new $this->controller, $this->method], $this->params);

		} else {
			/***** You can set this to default controller if you like *****/
			show_404('404 Page Not Found', 'The requested page does not found.');
		}	
	}
}


?>