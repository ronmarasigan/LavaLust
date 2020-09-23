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
 *  Required to execute neccessary functions
 * ------------------------------------------------------
 */
require_once SYSTEM_DIR . 'core/Registry.php';
require_once SYSTEM_DIR . 'core/Common.php';
/*
 * ------------------------------------------------------
 *  Instantiate the Benchmark class
 * ------------------------------------------------------
 */
$bm =& load_class('benchmark', 'core');
$bm->mark('total_execution_time_start');
$bm->mark('loading_time:_base_classes_start');

/*
 * ------------------------------------------------------
 *  APP constants accessible all over the site
 * ------------------------------------------------------
 */
require_once(APP_DIR . 'config/constants.php');

/*
 * ------------------------------------------------------
 * LavaLust BASE URL of your APPLICATION
 * ------------------------------------------------------
 */
define('BASE_URL', config_item('base_url'));

/*
 * ------------------------------------------------------
 *  Deployment Environment
 * ------------------------------------------------------
 */
switch (config_item('ENVIRONMENT'))
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

/*
 * ------------------------------------------------------
 *  Error Classes to show errors
 * ------------------------------------------------------
 */
function _handlers()
{
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');
}

/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
$router =& load_class('router', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the security class for xss and csrf support
 * ------------------------------------------------------
 */
$security =& load_class('security', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the Input/Ouput class
 * ------------------------------------------------------
 */
$io =& load_class('io', 'core');

/*
 * ------------------------------------------------------
 *  Load BaseController
 * ------------------------------------------------------
 */
require_once SYSTEM_DIR . 'core/Controller.php';

/*
 * ------------------------------------------------------
 *  Instantiate LavaLust Controller
 * ------------------------------------------------------
 */
function &get_instance()
{
  return Controller::get_instance();
}
$bm->mark('loading_time:_base_classes_end');

/*
 * ------------------------------------------------------
 *  Initiate Router
 * ------------------------------------------------------
 */
$router->initiate();
$bm->mark('total_execution_time_end');
?>