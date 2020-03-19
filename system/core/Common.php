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
 *  XSS Clean Function
 * ------------------------------------------------------
 */
function xss_clean($str, $is_image = FALSE)
{ 
	$security =& load_class('Security', 'core');
	return $security->xss_clean($str, $is_image);
}

/*
 * ------------------------------------------------------
 *  Class Loader Function
 * ------------------------------------------------------
 */
if ( ! function_exists('load_class'))
{
	function &load_class($class, $directory = '', $params = NULL) {

		$r = Registry::getInstance();
		$className = ucfirst(strtolower($class));

		//if the object already exists in the registry
		if($r->getObject($className) != NULL) {
			$object = $r->getObject($className);
			return $object;
		}
		
		$fullPathName = $directory  . '/' . $className . '.php';
				
		if (file_exists($fullPathName)) {
			require_once $fullPathName;
		}
		
		//put it in the registry
		$r->storeObject($class, new $className($params));
		
		$object = $r->getObject($class);
		return $object;
	}
}

/*
 * ------------------------------------------------------
 *  404 Error / Can be modified
 * ------------------------------------------------------
 */
function show_404($heading, $message, $page = NULL)
{
	$errors =& load_class('Errors', 'core');
	return $errors->show_404($heading, $message, $page);
}

/*
 * ------------------------------------------------------
 * Showing errors for debuging
 * ------------------------------------------------------
 */
function show_error($heading,$message,$error_code)
{
  	$errors =& load_class('Errors', 'core');
  	return $errors->show_error($heading,$message,$template = 'custom_errors',$error_code);
}

/*
 * ------------------------------------------------------
 * Showing errors for debuging
 * ------------------------------------------------------
 */
function shutdown()
{
	$last_error = error_get_last();
	if (isset($last_error) &&
		($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
	{
		errors($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
	}
}
/*
 * ------------------------------------------------------
 * Showing errors for debuging
 * ------------------------------------------------------
 */

function exceptions($e)
{
	$exception =& load_class('Errors', 'core');
	$exception->show_exception($e);
}

/*
 * ------------------------------------------------------
 * Showing errors for debuging
 * ------------------------------------------------------
 */
function errors($errno, $errstr, $errfile, $errline)
{
	$error =& load_class('Errors', 'core');
	$error->show_php_error($errno, $errstr, $errfile, $errline);
}