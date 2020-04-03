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
| @author       Ronald M. Marasigan
| @copyright    Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license      https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link     https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/

/*
* ------------------------------------------------------
*  Class Registry
* ------------------------------------------------------
*/
class Registry
{ 
	private $_classes = array();
	private static $_instance;
	
	private function __construct() { }
	
	private function __clone(){ }
	
    /*
    * ------------------------------------------------------
    *  Get Instance
    * ------------------------------------------------------
    */
    public static function get_instance()
    {
    	if(!isset(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /*
    * ------------------------------------------------------
    *  Get Class
    * ------------------------------------------------------
    */
    protected function get($key)
    {
    	
        if(isset($this->_classes[$key]))
        {	
            return $this->_classes[$key];
        }
        return NULL;
    }

    /*
    * ------------------------------------------------------
    *  Set Class
    * ------------------------------------------------------
    */
    protected function set($key,$object)
    {
        $this->_classes[$key] = $object;
    }
    
    /*
    * ------------------------------------------------------
    *  Get Class Object
    * ------------------------------------------------------
    */
    static function getObject($key)
    {
		return self::get_instance()->get($key);
	}

    /*
    * ------------------------------------------------------
    *  Store Class Object
    * ------------------------------------------------------
    */
	static function storeObject($key, $object)
	{
		return self::get_instance()->set($key,$object);
	}
}

