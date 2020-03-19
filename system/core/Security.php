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
| @author       Ronald M. Marasigan
| @copyright    Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license      https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link     https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/

/*
* ------------------------------------------------------
*  Class Security
* ------------------------------------------------------
*/
class Security
{
	private $security;

	/*
	* ------------------------------------------------------
	*  Escaping characters for a simple XSS Filter
	* ------------------------------------------------------
	*/
	public function xss_clean($string)
	{
		$htmlawed =& load_class('Htmlawed', SYSTEM_DIR .'libraries');
		return $htmlawed->filter($string);
	}
	
	/*
	* ------------------------------------------------------
	*  Generate CSRFToken to be loaded in a hidden form element in the view
	* ------------------------------------------------------
	*/
	public function CSRFToken()
	{
		$session =& load_class('Session', SYSTEM_DIR .'libraries');
		$session->set(['token' => bin2hex(random_bytes(32))]);
		return $session->get('token');
	}
	
	/**
	* ------------------------------------------------------
	*  Checking if the actual token and the submitted is equal
	* ------------------------------------------------------
	*/
	public function CSRFProtect($token)
	{
		$session =& load_class('Session', SYSTEM_DIR .'libraries');
		if(hash_equals($session->get('token'), $token))
			return true;
		else
			return false;
	}
}

?>