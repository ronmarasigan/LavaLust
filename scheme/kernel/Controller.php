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
 * Class Loader
 */
class Loader {
	/**
	 * @param mixed $class
	 * @param null $object_name
	 * 
	 * @return object
	 */
	public function model($class, $object_name = NULL)
	{
		if( ! class_exists('Model')) {
			require_once(SYSTEM_DIR.'kernel/Model.php');
		}
			
		$LAVA = Controller::instance();

		if(is_array($class))
		{
			foreach($class as $key => $value)
			{
				if(! is_int($key))
				{
					$LAVA->$key =& load_class($value, 'models', NULL, $key);
				} else {
					$LAVA->$value =& load_class($value, 'models', NULL, $value);
				}	
			}	
		} else {
			if(! is_null($object_name))
			{
				$LAVA->$object_name =& load_class($class, 'models', NULL, $object_name);
			} else {
				$LAVA->$class =& load_class($class, 'models');
			}
		}
			
	}
	
	/**
	 * Load View File
	 *
	 * @param [type] $viewFile
	 * @param array $data
	 * @return void
	 */
	public function view($viewFile, $data = array())
	{
		if(!empty($data))
			extract($data, EXTR_SKIP);
		ob_start();
		if(file_exists(APP_DIR .'views/' . $viewFile . '.php'))
			require_once(APP_DIR .'views/' . $viewFile . '.php');
		else
			throw new Exception(''.$viewFile.' view file did not exist.');
		echo ob_get_clean();
	}

	/**
	 * Load Helper
	 *
	 * @param [type] $helper
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
	 * @param [type] $classes
	 * @param array $params
	 * @return void
	 */
	public function library($classes, $params = array())
	{
		$LAVA = Controller::instance();
		if(is_array($classes))
		{
			foreach($classes as $class)
			{
				if($class == 'database') {
					$database =& load_class('database', 'database');
					$LAVA->db = $database::instance();
				}
				$LAVA->$class =& load_class($class, 'libraries', $params);
			}
		} else {
			if($classes == 'database') {
				$database =& load_class('database', 'database');
				$LAVA->db = $database::instance();
			}
			$LAVA->$classes =& load_class($classes, 'libraries', $params);
		}
	}

	/**
	 * Load Database
	 *
	 * @return void
	 */
	public function database()
	{
		$LAVA =& Controller::instance();
		$database =& load_class('database','database');
		$LAVA->db = $database::instance();
	}
}

/**
 * Class Controller
 */
class Controller extends Loader
{
	private static $instance;
	public $call, $var;
	
	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->call = $this;

		self::$instance = $this->call;

		foreach (loaded_class() as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		//load autoload config
		$autoload =& autoload_config();

		if(count($autoload['libraries']) > 0)
			$this->call->library($autoload['libraries']);
		if(count($autoload['models']) > 0)
			$this->call->model($autoload['models']);
		if(count($autoload['helpers']) > 0 )
			$this->call->helper($autoload['helpers']); 
	}
	
	/**
	 * Instance of controller
	 *
	 * @return object
	 */
	public static function &instance()
	{
		return self::$instance;
	}
	
}

?>