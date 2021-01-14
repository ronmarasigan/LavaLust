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

/*
 * ------------------------------------------------------
 *  Class Loader
 * ------------------------------------------------------
 */
class Loader {
	/*
	 * ------------------------------------------------------
	 *  Load Model
	 * ------------------------------------------------------
	 */
	public function model($classes)
	{
		if( ! class_exists('Model'))
			require_once(SYSTEM_DIR.'core/Model.php');

		$LAVA = Controller::get_instance();
		if(is_array($classes))
		{
			foreach($classes as $class)
				$LAVA->$class =& load_class($class . '_model', 'models');	
		}
		else
			$LAVA->$classes =& load_class($classes . '_model', 'models');
	}
	
	/*
	 * ------------------------------------------------------
	 *  Load View
	 * ------------------------------------------------------
	 */
	public function view($viewFile, $data = array())
	{
		if(!empty($data))
			extract($data, EXTR_SKIP);
		ob_start();
		if(file_exists(APP_DIR .'views/' . $viewFile . '.php'))
			require_once(APP_DIR .'views/' . $viewFile . '.php');
		else
			trigger_error($viewFile . ' view file is missing or does not exist!', E_USER_WARNING);
		echo ob_get_clean();
	}

	/*
	 * ------------------------------------------------------
	 *  Load Helpers
	 * ------------------------------------------------------
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
	
	/*
	 * ------------------------------------------------------
	 *  Load Library
	 * ------------------------------------------------------
	 */
	public function library($classes, $params = array())
	{
		$LAVA = Controller::get_instance();
		if(is_array($classes))
		{
			foreach($classes as $class)
			{
				if($class == 'database') {
					$database =& load_class('database', 'database');
					$LAVA->db = $database::get_instance();
				}
				$LAVA->$class =& load_class($class, 'libraries', $params);
			}
		} else {
			if($classes == 'database') {
				$database =& load_class('database', 'database');
				$LAVA->db = $database::get_instance();
			}
			$LAVA->$classes =& load_class($classes, 'libraries', $params);
		}
	}

	/*
	 * ------------------------------------------------------
	 *  Load Database
	 * ------------------------------------------------------
	 */
	public function database()
	{
		$LAVA =& Controller::get_instance();
		$database =& load_class('database','database');
		$LAVA->db = $database::get_instance();
	}

	/**
	 * ------------------------------------------------------
	 *  Check if Class is loaded
	 * -----------------------------------------------------
	 */
	public function is_loaded($class)
	{
		return array_search(ucfirst($class), is_loaded(), TRUE);
	}
}
/*
 * ------------------------------------------------------
 *  Class Controller that extends loader / singleton
 * ------------------------------------------------------
 */
class Controller extends Loader
{
	private static $instance;
	public $load, $var;
	
	public function __construct()
	{
		$this->load = $this;

		self::$instance = $this->load;

		foreach (is_loaded() as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		$autoload =& autoload_config();

		/*
		 * ------------------------------------------------------
		 *  Autoload Classes
		 * ------------------------------------------------------
		 */
		if(count($autoload['libraries']) > 0)
			$this->load->library($autoload['libraries']);
		if(count($autoload['models']) > 0)
			$this->load->model($autoload['models']);
		if(count($autoload['helpers']) > 0 )
			$this->load->helper($autoload['helpers']); 
	}
	
	public static function &get_instance()
	{
		return self::$instance;
	}
	
}

?>