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
 *  Required to execute neccessary functions
 * ------------------------------------------------------
 */
require_once SYSTEM_DIR . 'core/Registry.php';
require_once SYSTEM_DIR . 'core/Common.php';

/*
 * ------------------------------------------------------
 *  Deployment Environment
 * ------------------------------------------------------
 */
global $config;
switch ($config['ENV'])
{
	case 'development':
		error_handlers();
		error_reporting(-1);
		ini_set('display_errors', 1);	
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		error_reporting(0);
	break;

	default :
		error_handlers();
		error_reporting(-1);
		ini_set('display_errors', 1);
}

/*
 * ------------------------------------------------------
 *  Error Classes to log errors
 * ------------------------------------------------------
 */

function error_handlers()
{
	set_error_handler('errors');
	set_exception_handler('exceptions');
	register_shutdown_function('shutdown');
}

/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
$Router =& load_class('Router', SYSTEM_DIR . 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the Load the security class for xss and csrf support
 * ------------------------------------------------------
 */
$Security =& load_class('Security', SYSTEM_DIR . 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
$Input =& load_class('Input', SYSTEM_DIR . 'core');

/*
 * ------------------------------------------------------
 *  Load Controller and Base Model
 * ------------------------------------------------------
 */
require_once SYSTEM_DIR . 'core/Controller.php';
require_once SYSTEM_DIR . 'core/Model.php';
/*
 * ------------------------------------------------------
 *  Instantiate LavaLust Controller
 * ------------------------------------------------------
 */
function &get_instance()
{
  return Controller::get_instance();
}

/*
 * ------------------------------------------------------
 *  Initiate Router
 * ------------------------------------------------------
 */
$Router->initiate();
?>