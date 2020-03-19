<?php
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

/***** Prevent direct access to include file *****/
define('BASEPATH', true);

/***** Define Constants *****/
define('DIR',DIRECTORY_SEPARATOR);
define('ROOT_DIR',  __DIR__ . DIR);
define('APP_DIR', ROOT_DIR . 'application' . DIR);
define('SYSTEM_DIR', ROOT_DIR . 'system' . DIR);

/***** Required Files *****/
foreach (glob(ROOT_DIR . 'application/config/*.php') as $CFG) {
	require_once $CFG;
}

/***** Base URL *****/
define('BASE_URL', $config['base_url']);

/***** Route *****/
require_once(SYSTEM_DIR . 'LAVALust.php');
$LAVALust = new LAVALust();

/***** Auto load Base Class *****/
$LAVALust->autoload();

/***** Security / Input *****/
$Security =& load_class('Security', SYSTEM_DIR . 'core');
$Input =& load_class('Input', SYSTEM_DIR . 'core');

/***** Create route *****/
$LAVALust->initiate();

?>
