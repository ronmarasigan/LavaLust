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
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
* ------------------------------------------------------
*  Class Invoker
* ------------------------------------------------------
 */
class Invoker {
    /**
	 * Dynamic Properties using __set and __get
	 *
	 * @var array
	 */
	public $properties = [];

	/**
	 * Set Dynamic Properties
	 *
	 * @param string $prop
	 * @param string $val
	 */
	public function __set($prop, $val) {
		$this->properties[$prop] = $val;
	}

	/**
	 * Get Dynamic Properties
	 *
	 * @param string $prop
	 * @return void
	 */
	public function __get($prop) {
		if (array_key_exists($prop, $this->properties)) {
			return $this->properties[$prop];
		} else {
			throw new Exception("Undefined property $prop in class " . get_class($this));
		}
	}

    /**
	 * Hold class name
	 *
	 * @var mixed
	 */
	private $class;
	/**
	 * Load Controller
	 *
	 * @param mixed $class
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	public function controller($class, $method = 'index', $params = [])
	{
		$parts = explode('/', $class);
		$ctrl = ucfirst(array_pop($parts));
		$module = !empty($parts) ? array_shift($parts) : null;
		$nested = implode('/', $parts);

		// Module path
		if ($module) {
			$path = APP_DIR . "modules/{$module}/controllers/" . ($nested ? "{$nested}/" : '') . "{$ctrl}.php";
			if (file_exists($path)) {
				require_once $path;

				if (!class_exists($ctrl)) {
					throw new Exception("Controller class {$ctrl} not found in module {$module}");
				}

				$instance = new $ctrl();

				if (!method_exists($instance, $method)) {
					throw new Exception("Method {$method} not found in controller {$ctrl}");
				}

				return call_user_func_array([$instance, $method], $params);
			}
		}

		// App path
		$path = APP_DIR . "controllers/" . ($nested ? "{$nested}/" : '') . "{$ctrl}.php";
		if (file_exists($path)) {
			require_once $path;

			if (!class_exists($ctrl)) {
				throw new Exception("Controller class {$ctrl} not found");
			}

			$instance = new $ctrl();

			if (!method_exists($instance, $method)) {
				throw new Exception("Method {$method} not found in controller {$ctrl}");
			}

			return call_user_func_array([$instance, $method], $params);
		}

		$location = $module ? "module {$module}" . ($nested ? "/{$nested}" : '') : "app/controllers";
		throw new Exception("Controller {$ctrl} not found in {$location}");
	}

	/**
	 * @param mixed $class
	 * @param null $object_name
	 *
	 * @return object
	 */
	public function model($class, $object_name = null)
	{
		if (!class_exists('Model')) {
			require_once SYSTEM_DIR . 'kernel/Model.php';
		}

		$LAVA = lava_instance();

		if (is_array($class)) {
			foreach ($class as $key => $value) {
				if (is_int($key)) {
					$this->model($value);
				} else {
					$this->model($key, $value);
				}
			}
			return;
		}

		$parts = explode('/', $class);
		$this->class = array_pop($parts);
		$module = null;
		$nested = '';

		if (count($parts) > 0) {
			$module = array_shift($parts);
			$nested = implode('/', $parts);
		}

		$obj_name = $object_name ?? $this->class;

		if (isset($LAVA->properties[$obj_name])) {
			return $LAVA->properties[$obj_name];
		}

		if ($module) {
			$path = APP_DIR . "modules/{$module}/models/" . ($nested ? "{$nested}/" : '') . "{$this->class}.php";
			if (file_exists($path)) {
				require_once $path;
				$LAVA->properties[$obj_name] = new $this->class();
				return $LAVA->properties[$obj_name];
			}
		}

		$path = APP_DIR . "models/" . ($nested ? "{$nested}/" : '') . "{$this->class}.php";
		if (file_exists($path)) {
			require_once $path;
			$LAVA->properties[$obj_name] = new $this->class();
			return $LAVA->properties[$obj_name];
		}

		$location = $module ? "module {$module}" : "app/models";
		throw new Exception("Model {$this->class} not found in {$location}" . ($nested ? "/{$nested}" : ''));
	}


	/**
	 * Load View File
	 *
	 * @param string $view_file
	 * @param array $data
	 * @return void
	 */
	public function view($view_file, $data = NULL)
	{
		$LAVA = lava_instance();
		foreach (get_object_vars($LAVA) as $key => $val) {
			if (!isset($this->properties[$key])) {
				$this->properties[$key] = $LAVA->$key;
			}
		}

		if (!is_null($data)) {
			if (is_array($data)) {
				extract($data, EXTR_SKIP);
			} elseif (is_string($data)) {
				$$data = $data;
			} else {
				throw new RuntimeException('View parameter only accepts array or string types');
			}
		}

		ob_start();

		$view_file = str_replace('\\', '/', trim($view_file, '/'));

		$parts = explode('/', $view_file);
		$file = array_pop($parts);
		$module_or_nested = array_shift($parts);
		$nested = implode('/', $parts);

		$has_extension = pathinfo($file, PATHINFO_EXTENSION) !== '';
		$file_name = $has_extension ? $file : "{$file}.php";

		$app_dir = rtrim(APP_DIR, '/\\') . '/';

		$module_path = $app_dir . "modules/{$module_or_nested}/views/" . ($nested ? "{$nested}/" : '') . $file_name;
		if (file_exists($module_path)) {
			require $module_path;
			echo ob_get_clean();
			return;
		}

		$app_path = $app_dir . "views/" . ($nested ? "{$module_or_nested}/{$nested}/" : "{$module_or_nested}/") . $file_name;
		if (file_exists($app_path)) {
			require $app_path;
			echo ob_get_clean();
			return;
		}

		throw new RuntimeException("View {$view_file} not found in module or app/views");
	}


	/**
	 * Load Helper
	 *
	 * @param mixed $helper
	 * @return void
	 */
	public function helper($helper)
	{
		if ( is_array($helper) ) {
			foreach( array(APP_DIR . 'helpers', SYSTEM_DIR . 'helpers') as $dir )
			{
				foreach( $helper as $hlpr )
				{
					if ( file_exists($dir . DIRECTORY_SEPARATOR . $hlpr . '_helper.php') ) {
						require_once $dir . DIRECTORY_SEPARATOR . $hlpr . '_helper.php';
					}
				}
			}
		} else {
			foreach( array(APP_DIR . 'helpers', SYSTEM_DIR . 'helpers') as $dir )
			{
				if ( file_exists($dir . DIRECTORY_SEPARATOR . $helper . '_helper.php') )
				{
					require_once $dir . DIRECTORY_SEPARATOR . $helper . '_helper.php';
				}
			}
		}
	}

	/**
	 * Load Library
	 *
	 * @param mixed $classes
	 * @param array $params
	 * @return void
	 */
	public function library($classes, $params = NULL)
	{
		$LAVA = lava_instance();

		if (is_array($classes)) {
			foreach ($classes as $class) {
				if ($class == 'database') {
					$database = load_class('database', 'database');
					$LAVA->db = $database::instance(NULL);
				}

				if (isset($LAVA->properties[$class])) {
					continue;
				}

				$LAVA->properties[$class] = load_class($class, 'libraries');
			}
			return;
		}

		if (isset($LAVA->properties[$classes])) {
			return $LAVA->properties[$classes];
		}

		if ($classes == 'database') {
			$database = load_class('database', 'database');
			$LAVA->db = $database::instance(NULL);
		}

		$LAVA->properties[$classes] = load_class($classes, 'libraries', $params);
		return $LAVA->properties[$classes];
	}

	/**
	 * Load Database
	 *
	 * @param mixed $dbname
	 * @return void
	 */
	public function database($dbname = NULL)
	{
		$LAVA = lava_instance();

		if (is_null($dbname)) {

			if (isset($LAVA->db)) {
				return $LAVA->db;
			}

			$database = load_class('database', 'database');
			$LAVA->db = $database::instance(NULL);

			return $LAVA->db;
		}

		if (isset($LAVA->properties[$dbname])) {
			return $LAVA->properties[$dbname];
		}

		$database = load_class('database', 'database');
		$LAVA->properties[$dbname] = $database::instance($dbname);
		
		return $LAVA->properties[$dbname];
	}

	/**
	 * DBForge
	 *
	 * @return void
	 */
	public function dbforge()
	{
		$LAVA = lava_instance();
		$LAVA->properties['dbforge'] = load_class('dbforge','database');
	}

	/**
	 * Initializa
	 *
	 * @return void
	 */
    public function initialize()
    {
        $autoload = autoload_config();

		if(count($autoload['libraries']) > 0)
        {
            $this->library($autoload['libraries']);
        }
		if(count($autoload['models']) > 0)
        {
            $this->model($autoload['models']);
        }
		if(count($autoload['helpers']) > 0)
        {
            $this->helper($autoload['helpers']);
        }
		if(count($autoload['configs']) > 0)
        {
            lava_instance()->config->load($autoload['configs']);
        }
    }
}