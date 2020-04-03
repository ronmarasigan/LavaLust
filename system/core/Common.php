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
| @author 		Ronald M. Marasigan
| @copyright	Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license		https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link		https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/

/*
 * ------------------------------------------------------
 *  Class Loader Function
 * ------------------------------------------------------
 */
if ( ! function_exists('load_class'))
{
	function &load_class($class, $directory = '', $params = NULL) {

		$LAVA = Registry::get_instance();
		$className = ucfirst(strtolower($class));

		//if the object already exists in the registry
		if($LAVA->getObject($className) != NULL) {
			$object = $LAVA->getObject($className);
			return $object;
		}
		
		$fullPathName = $directory  . '/' . $className . '.php';
				
		if (file_exists($fullPathName)) {
			require_once $fullPathName;
		}
		
		//put it in the registry
		$LAVA->storeObject($class, isset($params) ? new $className($params) : new $className());
		
		$object = $LAVA->getObject($class);
		return $object;
	}
}

if ( ! function_exists('show_404'))
{
	/*
	 * ------------------------------------------------------
	 *  404 Error / Can be modified
	 * ------------------------------------------------------
	 */
	function show_404($heading, $message, $page = NULL)
	{
		$errors =& load_class('Errors', SYSTEM_DIR . 'core');
		return $errors->show_404($heading, $message, $page);
	}
}

if ( ! function_exists('show_error'))
{
	/*
	 * ------------------------------------------------------
	 * Showing errors for debuging
	 * ------------------------------------------------------
	 */
	function show_error($heading,$message,$error_code)
	{
	  	$errors =& load_class('Errors', SYSTEM_DIR . 'core');
	  	return $errors->show_error($heading,$message,$template = 'custom_errors',$error_code);
	}
}

if ( ! function_exists('shutdown'))
{
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
}

if ( ! function_exists('exceptions'))
{
	/*
	 * ------------------------------------------------------
	 * Showing errors for debuging
	 * ------------------------------------------------------
	 */
	function exceptions($e)
	{
		$exception =& load_class('Errors', SYSTEM_DIR . 'core');
		$exception->show_exception($e);
	}
}

if ( ! function_exists('errors'))
{
	/*
	 * ------------------------------------------------------
	 * Showing errors for debuging
	 * ------------------------------------------------------
	 */
	function errors($errno, $errstr, $errfile, $errline)
	{
		$error =& load_class('Errors', SYSTEM_DIR . 'core');
		$error->show_php_error($errno, $errstr, $errfile, $errline);
	}
}

if ( ! function_exists('noXSS'))
{
	/*
	 * ------------------------------------------------------
	 *  XSS Protection / Based on HTMLawed
	 * ------------------------------------------------------
	 */
	function noXSS($str, $is_image = FALSE)
	{ 
		$security =& load_class('Security', SYSTEM_DIR . 'core');
		return $security->xss_clean($str, $is_image);
	}
}

if ( ! function_exists('html_escape'))
{
	/*
	 * ------------------------------------------------------
	 *  Returns HTML escaped variable
	 * ------------------------------------------------------
	 */
	function html_escape($var, $double_encode = TRUE)
	{
		global $config;
		if (empty($var))
		{
			return $var;
		}

		if (is_array($var))
		{
			foreach (array_keys($var) as $key)
			{
				$var[$key] = html_escape($var[$key], $double_encode);
			}

			return $var;
		}

		return htmlspecialchars($var, ENT_QUOTES, $config['charset'], $double_encode);
	}
}