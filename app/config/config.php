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
| -------------------------------------------------------------------
|  Config Files
| -------------------------------------------------------------------
| This file is for setting-up default settings.
|
*/

/*
| -------------------------------------------------------------------
|  Your Own Configs
| -------------------------------------------------------------------
| For easy access on your config, just put them below
| You can simply get configs using config_item() function anywhere
| My Configs:
*/

/*
| -------------------------------------------------------------------
| LavaLust Version
| -------------------------------------------------------------------
*/
$config['VERSION']                 = '3.1.5';

/*
| -------------------------------------------------------------------
| Default Environment
| -------------------------------------------------------------------
| Values: development and production
*/
$config['ENVIRONMENT']             = 'development';

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your LavaLust root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| WARNING: You MUST set this value!
|
*/
$config['base_url'] 				= '';

/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
|
| If you are using mod_rewrite to remove index.php in the URL set this
| variable to blank.
|
*/
$config['index_page'] = 'index.php';

/*
|--------------------------------------------------------------------------
| Composer auto-loading
|--------------------------------------------------------------------------
|
| Enabling this setting will tell LavaLust to look for a Composer
| package auto-loader script in app/vendor/autoload.php.
|
|	$config['composer_autoload'] = TRUE;
|
| Or if you have your vendor/ directory located somewhere else, you
| can opt to set a specific path as well:
|
|	$config['composer_autoload'] = '/path/to/vendor/autoload.php';
|
| For more information about Composer, please visit http://getcomposer.org/
|
| Note: This will NOT disable or override the LavaLust-specific
|	autoloading (app/config/autoload.php)
*/
$config['composer_autoload']        = FALSE;

/*
|--------------------------------------------------------------------------
| Allowed URL Characters
|--------------------------------------------------------------------------
|
| This lets you specify which characters are permitted within your URLs.
| When someone tries to submit a URL with disallowed characters they will
| get a warning message.
|
| As a security measure you are STRONGLY encouraged to restrict URLs to
| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
|
| Leave blank to allow all characters -- but only if you are insane.
|
| The configured value is actually a regular expression character group
| and it will be executed as: ! preg_match('/^[<permitted_uri_chars>]+$/i
|
| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
|
*/
$config['permitted_uri_chars']		= 'a-z 0-9~%.:_\-';

/*
|--------------------------------------------------------------------------
| Default Character Set
|--------------------------------------------------------------------------
|
| This config will be use html_escape function
|
*/
$config['charset']					= 'UTF-8';

/*
|--------------------------------------------------------------------------
| Default Controllers and Methods
|--------------------------------------------------------------------------
|
| This config will be used in the Router Class inside kernel.
|
*/
$config['default_controller'] 		= 'Welcome';
$config['default_method'] 			= 'index';

/*
|--------------------------------------------------------------------------
| Error Views Directory Path
|--------------------------------------------------------------------------
|
| app/views/errors/ directory.  Use a full server path with trailing slash.
|
*/
$config['error_view_path']         	= APP_DIR . 'views' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR;
/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
|
| This determines which set of language files should be used. Make sure
| there is an available translation if you intend to use something other
| than en-US.
|
*/
$config['language'] 				= 'en-US';

/*
|--------------------------------------------------------------------------
| Session                     
|--------------------------------------------------------------------------
|
| Settings for sessions
| $config['sess_save_path'] will get the session save path form php.ini
| if empty.
|
|--------------------------------------------------------------------------
*/
$config['sess_driver']             = 'file';
$config['sess_cookie_name']        = 'LLSession';
$config['sess_expiration']         = 7200;
$config['sess_save_path']          = '';
$config['sess_match_ip']           = TRUE;
$config['sess_match_fingerprint']  = TRUE;
$config['sess_time_to_update']     = 300;
$config['sess_regenerate_destroy'] = TRUE;
$config['sess_expire_on_close']    = FALSE;

/*
|--------------------------------------------------------------------------
| Cookies                      
|--------------------------------------------------------------------------
|
|Settings for cookies.
|
|--------------------------------------------------------------------------
*/
$config['cookie_prefix']           = '';
$config['cookie_domain']           = '';
$config['cookie_path']             = '/';
$config['cookie_secure']           = FALSE;
$config['cookie_expiration']       = 86400;
$config['cookie_httponly']         = FALSE;
$config['cookie_samesite']         = 'Lax';

/*
|--------------------------------------------------------------------------
| Cache                      
|--------------------------------------------------------------------------
|
| Settings for Cache
| Set your cache directory and cache expiration time here
| Default:
|   $config['cache_dir'] = 'runtime/cache/';
|   $config['cache_default_expires'] = 0;
|
|--------------------------------------------------------------------------
*/
$config['cache_dir']               = 'runtime/cache/';
$config['cache_default_expires']   = 0;

/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
|
| 'csrf_exclude_uris' = Array of uris that will not go throught protection
| 'csrf_token_name' = The token name
| 'csrf_cookie_name' = The cookie name
| 'csrf_expire' = The number in seconds the token should expire.
*/
$config['csrf_protection']         = FALSE;
$config['csrf_exclude_uris']       = array();
$config['csrf_token_name']         = 'csrf_test_name';
$config['csrf_cookie_name']        = 'csrf_cookie_name';
$config['csrf_expire']             = 7200;
$config['csrf_regenerate']         = FALSE;
?>
