<?php
defined('BASEPATH') OR exit('Direct script access not allowed');
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
| @author     Ronald M. Marasigan
| @copyright  Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license    https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link   https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/

/*
* ------------------------------------------------------
*  Class Loader
* ------------------------------------------------------
*/
class Errors
{
	/*
	* ------------------------------------------------------
	*  Show 404 not found error
	* ------------------------------------------------------
	*/
	public function show_404($heading, $message, $page = NULL)
	{
		$page = isset($page) ? $page : '404';
		$heading = isset($heading) ? $heading : '404 Page Not Found';
		$message = isset($message) ? $message : 'The page you requested was not found.';
		$this->show_error($heading, $message, $page, 404);
	}

	/*
	* ------------------------------------------------------
	*  Show error for debugging
	* ------------------------------------------------------
	*/
	public function show_error($heading, $message, $template = 'custom_errors', $error_lvl_code = 500)
	{
		global $config;
		$template_path = $config['error_view_path'];

		if (empty($template_path))
		{
			$template_path = APP_DIR.'views/errors'.DIR;
		}
		http_response_code($error_lvl_code);
		require_once($template_path.$template.'.php');
		die();
	}
	/*
	* ------------------------------------------------------
	*  Show error for debugging
	* ------------------------------------------------------
	*/

	public function show_exception($exception)
	{
		global $config;
		$template_path = $config['error_view_path'];
		if (empty($template_path))
		{
			$template_path = APP_DIR.'views/errors'.DIR;
		}

		$message = $exception->getMessage();
		if (empty($message))
		{
			$message = '(null)';
		}

		require_once($template_path.'Exceptions.php');
		die();
	}

	/*
	* ------------------------------------------------------
	*  Show error for debugging
	* ------------------------------------------------------
	*/
	public function show_php_error($severity, $message, $filepath, $line)
	{
		global $config;
		$template_path = $config['error_view_path'];
		if (empty($template_path))
		{
			$template_path = APP_DIR.'views/errors'.DIR;
		}
		require_once($template_path.'Errors.php');
		die();
	}

}