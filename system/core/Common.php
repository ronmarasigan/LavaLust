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
 * @copyright Copyright 2020 (https://techron.info)
 * @version Version 1.2
 * @link https://lavalust.com
 * @license https://opensource.org/licenses/MIT MIT License
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
		foreach (array(APP_DIR, SYSTEM_DIR) as $path)
    	{
			$fullPathName = $path . $directory  . DIR . $className . '.php';
					
			if (file_exists($fullPathName)) {
				require_once $fullPathName;
			}
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

if ( ! function_exists('_shutdown_handler'))
{
	/*
	 * ------------------------------------------------------
	 * Showing errors for debuging
	 * ------------------------------------------------------
	 */
	function _shutdown_handler()
	{
		$last_error = error_get_last();
		if (isset($last_error) &&
			($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
		{
			_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}

if ( ! function_exists('_exception_handler'))
{
	/*
	 * ------------------------------------------------------
	 * Showing errors for debuging
	 * ------------------------------------------------------
	 */
	function _exception_handler($e)
	{
		$exception =& load_class('Errors', 'core');
		$exception->show_exception($e);
	}
}

if ( ! function_exists('_error_handler'))
{
	/*
	 * ------------------------------------------------------
	 * Showing errors for debuging
	 * ------------------------------------------------------
	 */
	function _error_handler($errno, $errstr, $errfile, $errline)
	{
		$error =& load_class('Errors', 'core');
		$error->show_php_error($errno, $errstr, $errfile, $errline);
	}
}

if ( ! function_exists('get_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main config.php file
	 * ------------------------------------------------------
	 */
	function &get_config()
	{
		static $config;

		if ( file_exists(APP_DIR.'config/config.php') ) 
		{
			require_once APP_DIR.'config/config.php';

			if ( isset($config) OR is_array($config) ) 
			{
				foreach( $config as $key => $val ) 
				{
					$config[$key] = $val;
				}

				return $config;
			}
		} else
			throw new Exception("The configuration file does not exist");
	}
}

if ( ! function_exists('autoload_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main autolaod.php file
	 * This is for autoloading of libraries, models, and helpers file
	 * ------------------------------------------------------
	 */
	function &autoload_config()
	{
		static $autoload;

		if ( file_exists(APP_DIR.'config/autoload.php') ) 
		{
			require_once APP_DIR.'config/autoload.php';

			if ( isset($autoload)  OR is_array($config) ) 
			{
				foreach( $autoload as $key => $val ) 
				{
					$autoload[$key] = $val;
				}

				return $autoload;
			}
		} else
			throw new Exception("The configuration file does not exist");
	}
}

if ( ! function_exists('database_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main database.php file
	 * Note: This will be used commonly inside Model file
	 * in the core folder
	 * ------------------------------------------------------
	 */
	function &database_config()
	{
		static $database;

		if ( file_exists(APP_DIR.'config/database.php') ) 
		{
			require_once APP_DIR.'config/database.php';

			if ( isset($database)  OR is_array($config) )
			{
				foreach( $database as $key => $val ) 
				{
					$database[$key] = $val;
				}

				return $database;
			}
		} else
			throw new Exception("The configuration file does not exist");
	}
}

if ( ! function_exists('route_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main routes.php file
	 * ------------------------------------------------------
	 */
	function &route_config()
	{
		static $route;

		if ( file_exists(APP_DIR.'config/routes.php') ) 
		{
			require_once APP_DIR.'config/routes.php';

			if ( isset($route)  OR is_array($config) )
			{
				foreach( $route as $key => $val ) 
				{
					$route[$key] = $val;
				}

				return $route;
			}
		} else
			throw new Exception("The configuration file does not exist");
	}
}

function get_mime_type($extension) {

    $mimes = array( 
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',

        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    if (isset( $mimes[$extension] )) {
     return $mimes[$extension];
    } else {
     return 'application/octet-stream';
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