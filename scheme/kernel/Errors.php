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
/*

/**
 * Class Error
 */
class Errors
{
	/**
	 * Show 404 Page Not Found
	 *
	 * @param string $heading
	 * @param string $message
	 * @param string $page
	 * @return void
	 */
	public function show_404($heading, $message, $template)
	{
		$template = ! empty($template) ? $template : 'error_404';
		$heading =  ! empty($heading) ? $heading : '404 Page Not Found';
		$message =  ! empty($message) ? $message : 'The page you requested was not found.';
		$this->show_error($heading, $message, $template, 404);
	}

	/**
	 * Show error for debugging
	 *
	 * @param string $heading
	 * @param string $message
	 * @param string $template
	 * @param integer code
	 * @return void
	 */
	public function show_error($heading, $message, $template, $code = 500)
	{
		$template_path = config_item('error_view_path');
		if (empty($template_path))
		{
			$template_path = APP_DIR.'views/errors'.DIRECTORY_SEPARATOR;
		}

		http_response_code($code);
		require_once($template_path.$template.'.php');
		exit();
	}

	/**
	 * Show Exception error for debugging
	 *
	 * @param object $exception
	 * @return void
	 */
	public function show_exception($exception)
	{
		$template_path = config_item('error_view_path');
		if (empty($template_path))
		{
			$template_path = APP_DIR.'views/errors'.DIRECTORY_SEPARATOR;
		}

		$message = $exception->getMessage();
		if (empty($message))
		{
			$message = '(null)';
		}

		require_once($template_path.'error_exception.php');
		exit();
	}

	/**
	 * Show PHP error for debugging
	 *
	 * @param string $severity
	 * @param string $message
	 * @param string $filepath
	 * @param string $line
	 * @return void
	 */
	public function show_php_error($severity, $message, $filepath, $line)
	{
		$template_path = config_item('error_view_path');
		if (empty($template_path))
		{
			$template_path = APP_DIR.'views/errors'.DIRECTORY_SEPARATOR;
		}
		require_once($template_path.'error_php.php');
		die();
	}

}