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
| -------------------------------------------------------------------
|  Config Files
| -------------------------------------------------------------------
| This file is for setting-up default settings.
|
*/
/*
| -------------------------------------------------------------------
| Default Environment
| -------------------------------------------------------------------
*/
$config['ENV']                     = 'production';

/*
| -------------------------------------------------------------------
| Default Timezone
| -------------------------------------------------------------------
*/
date_default_timezone_set('Asia/Manila');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| WARNING: You MUST set this value!
|
*/
$config['base_url'] 				= 'http://techron.info/';

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
$config['permitted_uri_chars']     = 'a-z 0-9~%.:_\-';

/*
|--------------------------------------------------------------------------
| Default Controllers and Methods
|--------------------------------------------------------------------------
|
| This config will be used in the babaeron.php file inside core.
|
*/
$config['default_controller'] 		= 'Main';
$config['default_method'] 			= 'index';

/*
|--------------------------------------------------------------------------
| Error Views Directory Path
|--------------------------------------------------------------------------
|
| application/views/errors/ directory.  Use a full server path with trailing slash.
|
*/
$config['error_view_path']         	= APP_DIR . 'views'.DIR.'errors'.DIR;
/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
|
| This determines which set of language files should be used. Make sure
| there is an available translation if you intend to use something other
| than english.
|
*/
$config['language'] 				= 'en';

/*
|--------------------------------------------------------------------------
| Session                     
|--------------------------------------------------------------------------
|
|Settings for sessions
|
|--------------------------------------------------------------------------
*/
$config['encryption_key']          = 'E0i3SfNtntaypu2owlxqdmXBtZ6i0NDm';
$config['sess_driver']             = ''; // Options: database or file
$config['sess_cookie_name']        = 'kiss_session';
$config['sess_expiration']         = 7200;
$config['sess_save_path']          = ''; // APPPATH.'sessions' or kiss_sessions
$config['sess_match_ip']           = FALSE;
$config['sess_time_to_update']     = 300;
$config['sess_regenerate_destroy'] = FALSE;
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
$config['cookie_expiration']       = 86400; // 86400 - Seconds in 1 day
$config['cookie_httponly']         = TRUE;
?>