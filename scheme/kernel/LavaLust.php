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
 * Required to execute neccessary functions
 */
require_once SYSTEM_DIR . 'kernel/Registry.php';
require_once SYSTEM_DIR . 'kernel/Routine.php';

/**
 * LavaLust BASE URL of your APPLICATION
 */
define('BASE_URL', config_item('base_url'));

/**
 * Composer (Autoload)
 */
if ($composer_autoload = config_item('composer_autoload'))
{
	if ($composer_autoload === TRUE)
	{
		file_exists(APP_DIR.'vendor/autoload.php')
			? require_once(APP_DIR.'vendor/autoload.php')
			: show_404('404 Not Found', 'Composer config file not found.');
	}
	elseif (file_exists($composer_autoload))
	{
		require_once($composer_autoload);
	}
	else
	{
		show_404('404 Not Found', 'Composer config file not found.');
	}
}

/**
 * Instantiate the Benchmark class
 */
$performance =& load_class('performance', 'kernel');
$performance->tag('lavalust');

/**
 * Deployment Environment
 */
switch (strtolower(config_item('ENVIRONMENT')))
{
	case 'development':
		_handlers();
		error_reporting(-1);
		ini_set('display_errors', 1);	
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		error_reporting(0);
	break;

	default :
		_handlers();
		error_reporting(-1);
		ini_set('display_errors', 1);
}

/**
 * Error Classes to show errors
 *
 * @return void
 */
function _handlers()
{
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');
}



/**
 * Instantiate the security class for xss and csrf support
 */
$security =& load_class('security', 'kernel');

/**
 * Instantiate the Input/Ouput class
 */
$io =& load_class('io', 'kernel');

/**
 * Instantiate the Language class
 */
$lang =& load_class('lang', 'kernel');

/**
 * Load BaseController
 */
require_once SYSTEM_DIR . 'kernel/Controller.php';

/**
 * Instantiate the routing class and set the routing
 */
$router =& load_class('router', 'kernel', array(new Controller));
require_once APP_DIR . 'config/routes.php';

/**
 * Instantiate LavaLust Controller
 *
 * @return object
 */
function &lava_instance()
{
  return Controller::instance();
}
$performance->tag('lavalust');

// Handle the request
$url = $router->sanitize_url(str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']));
$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
$router->initiate($url, $method);
?>