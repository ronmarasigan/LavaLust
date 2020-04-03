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
| @author       Ronald M. Marasigan
| @copyright    Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license      https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link     https://github.com/BABAERON/LAVALust-MVC-Framework
|
*/

/*
* ------------------------------------------------------
*  Class Session
* ------------------------------------------------------
*/

class Session {

	function __construct()
	{

		/*
	    * ------------------------------------------------------
	    *  The session is started automatically with the constructor
	    * ------------------------------------------------------
	    */
		if (session_status() === PHP_SESSION_NONE)
		{
			session_start();
			$_SESSION['SESSION_CREATED_TIMESTAMP'] = time();
		}
	}

	/*
    * ------------------------------------------------------
    *  Check if the current session is active
    * ------------------------------------------------------
    */
	function isActive()
	{
		if(session_status() === PHP_SESSION_NONE || session_status() === PHP_SESSION_ACTIVE) {
			if(time() - $_SESSION['SESSION_CREATED_TIMESTAMP'] < 1800) {
				$_SESSION['SESSION_CREATED_TIMESTAMP'] = time();
				return true;
			}
		}
		return false;
	}
	
	/*
    * ------------------------------------------------------
    *  Regenerate new session ID
    * ------------------------------------------------------
    */
	function regenerateId($deleteOldSession = true)
	{
		return session_regenerate_id($deleteOldSession);
	}
	

	/*
    * ------------------------------------------------------
    *  Return session id
    * ------------------------------------------------------
    */
	function sessionId()
	{
		return session_id();
	}

	/*
    * ------------------------------------------------------
    *  Check if session has key variable
    * ------------------------------------------------------
    */
	function hasVariable($key = null)
	{
		if(!$this->isActive())
			throw new Exception('Session is not active or none existing');
		
		if(!is_null($key)) {
			if(isset($_SESSION[$key]))
				return true;
		}
		return false;
	}
	
	/*
    * ------------------------------------------------------
    *  Set session array
    * ------------------------------------------------------
    */
	function set($keys = array())
	{
		if(!$this->isActive())
			return $this;
		
		$keys['SID'] = $this->sessionId();
		foreach($keys as $key => $val)
		{
			$_SESSION[$key] = $val;
		}
	}
	
	/*
    * ------------------------------------------------------
    *  Unset session array
    * ------------------------------------------------------
    */
	function unset($data = array())
	{
		if(!$this->isActive())
			return $this;
		
		foreach ($data as $key) {
			if($this->hasVariable($key))
				unset($_SESSION[$key]);
		}
	}
	
	/*
    * ------------------------------------------------------
    *  Get specific session key value
    * ------------------------------------------------------
    */
	function get($key)
	{
		if(!$this->isActive())
			throw new Exception('Session is not active or none existing');
		
	  return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}
	
	/*
    * ------------------------------------------------------
    *  Destroy the session permanently
    * ------------------------------------------------------
    */
	function destroy()
	{
		session_unset();
		session_destroy();
		session_write_close();
		setcookie(session_name(), '', time() - 300);
		$_SESSION = array();
	}

}

?>