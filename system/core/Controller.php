<?php
defined('BASEPATH') OR exit('No direct script access allowed');
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
		$c = Controller::getInstance();
		if(is_array($classes))
		{
			foreach($classes as $class)
				$c->$class =& load_class($class . '_model', APP_DIR . 'models');	
		}
		else
			$c->$classes =& load_class($classes . '_model', APP_DIR . 'models');	
	}
	
	/*
	 * ------------------------------------------------------
	 *  Load View
	 * ------------------------------------------------------
	 */
	public function view($viewFile, $params=NULL, $val = NULL)
	{
		if(!empty($params))
		{
			foreach($params as $key => $val)
			{
				$this->pageVars[$key] = $val;
			}
			extract($this->pageVars);
		}
		ob_start();
		if(file_exists(APP_DIR .'views/' . $viewFile . '.php'))
			require(APP_DIR .'views/' . $viewFile . '.php');
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
			foreach( array(APP_DIR.'helpers',SYSTEM_DIR.'helpers') as $dir ) {
				foreach( $helper as $hlpr ) {
					if ( file_exists($dir.DIR.$hlpr.'_helper.php') ) {
						require_once $dir.DIR.$hlpr.'_helper.php';
					}
				}
			}
		} else {
			foreach( array(APP_DIR.'helpers',SYSTEM_DIR.'helpers') as $dir ) {
				if ( file_exists($dir.DIR.$helper.'_helper.php') ) {
					require_once $dir.DIR.$helper.'_helper.php';
				}
			}
		}
	}
	
	/*
	 * ------------------------------------------------------
	 *  Load Helper
	 * ------------------------------------------------------
	 */
	public function library($classes)
	{
		$c = Controller::getInstance();
		if(is_array($classes))
		{
			foreach($classes as $class)
				$c->$class =& load_class($class, SYSTEM_DIR . 'libraries');
		}
		else
			$c->$classes =& load_class($classes, SYSTEM_DIR . 'libraries');
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
	public $load;
	
	public function __construct()
	{
		$this->load = $this;
		self::$instance = $this->load;

		global $autoload;

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
	
	public static function getInstance()
	{
		return self::$instance;
	}
	
}

?>