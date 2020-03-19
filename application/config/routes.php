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
| @author 		Ronald M. Marasigan
| @copyright	Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license		https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link		https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/

/**
 * ===========================================
 *  URI ROUTING
 * ===========================================
 *
 *  Set your custom routes.
 * 
 * ===========================================
 */

$route['404_override']       = '';
//$route['default_controller'] = 'Main/index';
//$route['number/:num/:num'] = 'Welcome/index/$1/$2';
$route['Covid-19/Case-in-PH'] = 'Main/caseph';
$route['Covid-19/Case-out-PH'] = 'Main/caseoutph';
$route['Covid-19/Suspected-Cases'] = 'Main/suspectedcases';
$route['Covid-19/Under-Observation-Cases'] = 'Main/underobservation';
$route['Covid-19/Checkpoints'] = 'Main/checkpoints';
$route['Covid-19/Information-Sources'] = 'Main/sources';