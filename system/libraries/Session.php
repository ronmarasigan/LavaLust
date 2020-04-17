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
*  Class Session
* ------------------------------------------------------
*/

class Session {

	private $config;

	public function __construct()
	{
		$this->config = get_config();
		/*
	    * ------------------------------------------------------
	    *  Default configs
	    * ------------------------------------------------------
	    */
        ini_set('session.cookie_lifetime', 0);

        ini_set('session.cookie_httponly', 1);

        ini_set('session.use_only_cookies', 1);

        ini_set('session.use_strict_mode', 1);

        ini_set("session.gc_maxlifetime", $this->config['sess_expiration']);

		ini_set("session.gc_divisor", 1);

		ini_set("session.gc_probability", 1);

		ini_set("session.save_path", $this->config['sess_save_path']);

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')

        	ini_set('session.cookie_secure', 1);

		/*
	    * ------------------------------------------------------
	    *  The session is started automatically with the constructor
	    * ------------------------------------------------------
	    */
		if (session_status() === PHP_SESSION_NONE)
		{
			session_name($this->config['sess_cookie_name']);
			session_start();
			$_SESSION['SESSION_CREATED_TIMESTAMP'] = time();
		}
	}

	/*
    * ------------------------------------------------------
    *  Check if the current session is active
    * ------------------------------------------------------
    */
	public function isActive()
	{
		if(session_status() === PHP_SESSION_NONE || session_status() === PHP_SESSION_ACTIVE) {
			if(time() - $_SESSION['SESSION_CREATED_TIMESTAMP'] < $this->config['sess_timeout'] ) {
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
	public function regenerateId($deleteOldSession = true)
	{
		return session_regenerate_id($deleteOldSession);
	}
	

	/*
    * ------------------------------------------------------
    *  Return session id
    * ------------------------------------------------------
    */
	public function sessionId()
	{
		return session_id();
	}

	/*
    * ------------------------------------------------------
    *  Check if session has key variable
    * ------------------------------------------------------
    */
	public function hasVariable($key = null)
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
	public function set($keys = array())
	{
		if(!$this->isActive())
			return $this;
		
		$keys['SID'] = $this->sessionId();

		if(is_array($keys))
		{
			foreach($keys as $key => $val)
			{
				$_SESSION[$key] = $val;
			}
		} else {
			throw new Exception('Supplied variable is empty or is not an array');
		}
	}
	
	/*
    * ------------------------------------------------------
    *  Unset session array
    * ------------------------------------------------------
    */
	public function unset($keys = array())
	{
		if(!$this->isActive())
			return $this;
		if(is_array($keys))
		{
			foreach ($keys as $key) {
				if($this->hasVariable($key))
					unset($_SESSION[$key]);
			}
		} else {
			throw new Exception('Supplied variable is empty or is not an array');
		}
	}
	
	/*
    * ------------------------------------------------------
    *  Get specific session key value
    * ------------------------------------------------------
    */
	public function get($key)
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
	public function destroy()
	{
		session_unset();
		session_destroy();
		session_write_close();
		set_cookie($this->config['sess_cookie_name'], '', time() - 300);
		$_SESSION = array();
	}

}

?>