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
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * Required to execute neccessary functions
 */
require_once SYSTEM_DIR . 'kernel/Registry.php';
require_once SYSTEM_DIR . 'kernel/Routine.php';

/**
 * Check and load .env file if any
 */
if (file_exists(ROOT_DIR . '.env')) {
    foreach (file(ROOT_DIR . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        // Skip comments and lines without =
        if ($line[0] === '#' || !str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Validate key format
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) continue;

        // Strip surrounding quotes
        if (strlen($value) >= 2 && in_array($value[0], ['"', "'"], true) && $value[-1] === $value[0]) {
            $value = substr($value, 1, -1);
        }

        putenv("$key=$value");
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
}

/**
 * LavaLust BASE URL of your APPLICATION
 */
define('BASE_URL', config_item('base_url'));

/**
 * Date Default Timezone
 */
date_default_timezone_set(config_item('date_default_timezone'));

/**
 * Composer (Autoload)
 */
if ($composer_autoload = config_item('composer_autoload'))
{
	if ($composer_autoload === TRUE)
	{
		file_exists(ROOT_DIR.'vendor/autoload.php')
			? require_once(ROOT_DIR.'vendor/autoload.php')
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
$performance = load_class('performance', 'kernel');
$performance->start('lavalust');

/**
 * Define CLI mode
 *
 * @var bool
 */
define('IS_CLI', php_sapi_name() === 'cli');

/**
 * Deployment Environment
 */
switch (strtolower(config_item('environment')))
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
		_handlers();
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
 * Instantiate the config class
 */
$config = load_class('config', 'kernel');

/**
 * Instantiate the logger class
 */
$logger = load_class('logger', 'kernel');

/**
 * Instantiate the security class for xss and csrf support
 */
$security = load_class('security', 'kernel');

/**
 * Instantiate the Input/Ouput class (deprecate in v4.7.0)
 */
$io = load_class('io', 'kernel');

/**
 * Instantiate the Request class
 */
$request = load_class('request', 'kernel');

/**
 * Instantiate the Response class
 */
$response = load_class('response', 'kernel');

/**
 * Instantiate the Language class
 */
$lang = load_class('lang', 'kernel');

/**
 * Instantiate the Proxies classes
 */
if(config_item('proxy_enabled')){
	require_once SYSTEM_DIR . 'kernel/Proxies.php';
}

/**
 * Load BaseController
 */
require_once SYSTEM_DIR . 'kernel/Controller.php';

/**
 * Instantiate the routing class and set the routing
 */
$router = load_class('router', 'kernel', array(new Controller));

lava_instance()->router = $router;

require_once APP_DIR . 'config/routes.php';

/**
 * Attribute-based routing (PHP >= 8.0 only)
 *
 * Auto-discovers controllers under app/controllers/ that carry
 * #[Route]/#[Get]/#[Post]/etc attributes and registers them onto
 * the same $router instance used above. Controllers with no
 * attributes (e.g. Welcome.php) are left completely untouched, and
 * routes.php remains the manual registration path — nothing here
 * is required to keep using string-callback routes.
 *
 * On PHP 7.4 (LavaLust's stated minimum) this block is skipped
 * silently since #[Attribute] syntax doesn't parse pre-8.0.
 */
if (PHP_VERSION_ID >= 80000) {
    require_once SYSTEM_DIR . 'kernel/Attributes.php';
    require_once SYSTEM_DIR . 'kernel/AttributeRouteLoader.php';
    (new AttributeRouteLoader($router))->load();
}

/**
 * Load app-level kernel extensions (MY_* overrides)
 * Allows extending any kernel class without touching core files.
 */
if (is_dir(APP_DIR . 'kernel/')) {
    foreach (glob(APP_DIR . 'kernel/'.config_item('subclass_prefix').'*.php') as $my_file) {
        require_once $my_file;
    }
}

/**
 * Instantiate LavaLust Controller
 *
 * @return object
 */
function lava_instance()
{
  	return Controller::instance();
}
$performance->stop('lavalust');

if (php_sapi_name() === 'cli') {
    $raw_url = $argv[1] ?? '';
    
    $raw_url = ltrim($raw_url, '/');
    
    $url = $router->sanitize_url($raw_url);
    
    if (empty($url)) {
        $url = '/';
    } else {
        $url = '/' . $url;
    }
    
    $method = 'GET';
    
} else {
    $base  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
	$path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$url   = $router->sanitize_url(substr($path, strlen($base)) ?: '/');
    $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
}

if (empty($url)) $url = '/';

$router->initiate($url, $method);
?>