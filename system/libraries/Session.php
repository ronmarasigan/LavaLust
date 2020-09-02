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
		/*
	    * ------------------------------------------------------
	    *  The session Configs
	    * ------------------------------------------------------
	    */
		$this->config = get_config();

		if ( ! empty($this->config['cookie_prefix']) ) {
	    	$this->config['cookie_name'] = $this->config['sess_cookie_name'] ? $this->config['cookie_prefix'].$this->config['sess_cookie_name'] : NULL;
	    } else {
	    	$this->config['cookie_name'] = $this->config['sess_cookie_name'] ? $this->config['sess_cookie_name'] : NULL;
	    }

	    if (empty($this->config['cookie_name'])) {
	    	$this->config['cookie_name'] = ini_get('session.name');
	    } else {
	    	ini_set('session.name', $this->config['cookie_name']);
	    }

	    if (empty($this->config['sess_expiration'])) {
	    	$this->config['sess_expiration'] = (int) ini_get('session.gc_maxlifetime');
	    } else {
	    	$this->config['sess_expiration'] = (int) $this->config['sess_expiration'];
	    	ini_set('session.gc_maxlifetime', $this->config['sess_expiration']);
	    }

	    if (isset($this->config['cookie_expiration']))
	    	$this->config['cookie_expiration'] = (int) $this->config['cookie_expiration'];
	    else
	    	$this->config['cookie_expiration'] = ( ! isset($this->config['sess_expiration']) AND $this->config['sess_expire_on_close']) ? 0 : (int) $this->config['sess_expiration'];

	    session_set_cookie_params(
	    	$this->config['cookie_expiration'],
	    	$this->config['cookie_path'],
	    	$this->config['cookie_domain'],
	    	$this->config['cookie_secure'],
	    	TRUE
	    );
	    ini_set('session.use_trans_sid', 0);
	    ini_set('session.use_strict_mode', 1);
	    ini_set('session.use_cookies', 1);
	    ini_set('session.use_only_cookies', 1);
	    ini_set('session.sid_length', $this->_get_sid_length());

	    if ( ! empty($this->config['sess_driver']) AND $this->config['sess_driver'] == 'file' ) {
			require_once 'Session/FileSessionHandler.php';
			$handler = new FileSessionHandler();
			session_set_save_handler($handler, TRUE);
		}
		
	    session_start();

	    if ( isset($_COOKIE[$this->config['cookie_name']]) ) {
	    	preg_match('/('.session_id().')/', $_COOKIE[$this->config['cookie_name']], $matches);
	    	if ( empty($matches) ) {
	        	unset($_COOKIE[$this->config['cookie_name']]);
	      	}
	    }

	    $regenerate_time = (int) $this->config['sess_time_to_update'];
	    if ( (empty($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') AND ($regenerate_time > 0) ) {
	    	if ( ! isset($_SESSION['last_session_regenerate'])) {
	        	$_SESSION['last_session_regenerate'] = time();
	    	} elseif ( $_SESSION['last_session_regenerate'] < (time() - $regenerate_time) ) {
		        $this->sess_regenerate((bool) $this->config['sess_regenerate_destroy']);
	      }
	    } elseif (isset($_COOKIE[$this->config['cookie_name']]) AND $_COOKIE[$this->config['cookie_name']] === session_id()) {
	    	setcookie(
		        $this->config['cookie_name'],
		        session_id(),
		        (empty($this->config['cookie_expiration']) ? 0 : time() + $this->config['cookie_expiration']),
		        $this->config['cookie_path'],
		        $this->config['cookie_domain'],
		        $this->config['cookie_secure'],
		        TRUE
		      );
	    }

	    $this->_lava_init_vars();
	}


	protected function _lava_init_vars()
	{
		if ( ! empty($_SESSION['__lava_vars']))
		{
			$current_time = time();

			foreach ($_SESSION['__lava_vars'] as $key => &$value)
			{
				if ($value === 'new')
				{
					$_SESSION['__lava_vars'][$key] = 'old';
				}
				elseif ($value < $current_time)
				{
					unset($_SESSION[$key], $_SESSION['__lava_vars'][$key]);
				}
			}

			if (empty($_SESSION['__lava_vars']))
			{
				unset($_SESSION['__lava_vars']);
			}
		}

		$this->userdata =& $_SESSION;
	}

	/**
	 * Destroy Browser Cookie
	 * @return bool TRUE if destroyed
	 */
	protected function _destroy_cookie()
	{
		return setcookie(
			$this->config['cookie_name'],
			NULL,
			1,
			$this->config['cookie_path'],
			$this->config['cookie_domain'],
			$this->config['cookie_secure'],
			TRUE
		);
	}

	/**
	 * SID length
	 * @return int SID length
	 */
	private function _get_sid_length()
	{
		$bits_per_character = (int) ini_get('session.sid_bits_per_character');
		$sid_length = (int) ini_get('session.sid_length');
		if (($bits = $sid_length * $bits_per_character) < 160)
			$sid_length += (int) ceil((160 % $bits) / $bits_per_character);
		return $sid_length;
	}
	
	/**
	 * Regenerate Session ID
	 * @param  bool FALSE by Default
	 * @return string    Session ID
	 */
	public function sess_regenerate($destroy = FALSE)
	{
		$_SESSION['last_session_regenerate'] = time();
		session_regenerate_id($destroy);
	}

	/**
	 * Mark as Flash
	 * @param  string $key Session
	 * @return bool
	 */
	public function mark_as_flash($key)
	{
		if (is_array($key))
		{
			for ($i = 0, $c = count($key); $i < $c; $i++)
			{
				if ( ! isset($_SESSION[$key[$i]]))
				{
					return FALSE;
				}
			}

			$new = array_fill_keys($key, 'new');

			$_SESSION['__lava_vars'] = isset($_SESSION['__lava_vars'])
				? array_merge($_SESSION['__lava_vars'], $new)
				: $new;

			return TRUE;
		}

		if ( ! isset($_SESSION[$key]))
		{
			return FALSE;
		}

		$_SESSION['__lava_vars'][$key] = 'new';
		return TRUE;
	}
   	
   	/**
   	 * Return Session ID
   	 * @return string Session ID
   	 */
	public function session_id()
	{
		return session_id();
	}

	/**
	 * Check if session variable has data
	 * @param  string $key Session
	 * @return boolean
	 */
	public function has_userdata($key = null)
	{
		if(!is_null($key)) {
			if(isset($_SESSION[$key]))
				return true;
		}
		return false;
	}
	
	/**
	 * Set Data to Session Key
	 * @param array $keys array of Sessions
	 */
	public function set_userdata($keys = array())
	{
		$keys['SID'] = $this->session_id();

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
	
	/**
	 * Unset Session Data
	 * @param  array  $keys Array of Sessions
	 * @return function
	 */
	public function unset_userdata($keys = array())
	{
		if(is_array($keys))
		{
			foreach ($keys as $key) {
				if($this->has_userdata($key))
					unset($_SESSION[$key]);
			}
		} else {
			throw new Exception('Supplied variable is empty or is not an array');
		}
	}
	
   	/**
   	 * Get specific session key value
   	 * @param  array $key Session Keys
   	 * @return string      Session Data
   	 */
	public function get_userdata($key)
	{
	  return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}
	
	/**
	 * Session Destroy
	 * @return function
	 */
	public function sess_destroy()
	{
		session_destroy();
	}

	/**
	 * Get flash data to Session
	 * @param  array $key Session Keys
	 * @return string      Session Data
	 */
	public function flashdata($key = NULL)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__lava_vars'], $_SESSION['__lava_vars'][$key], $_SESSION[$key]) && ! is_int($_SESSION['__lava_vars'][$key]))
				? $_SESSION[$key]
				: NULL;
		}

		$flashdata = array();

		if ( ! empty($_SESSION['__lava_vars']))
		{
			foreach ($_SESSION['__lava_vars'] as $key => &$value)
			{
				is_int($value) OR $flashdata[$key] = $_SESSION[$key];
			}
		}

		return $flashdata;
	}

	/**
	 * Get flash data to Session
	 * @param  array $key Session Keys
	 * @return function
	 */
	public function set_flashdata($data, $value = NULL)
	{
		$this->set_userdata($data, $value);
		$this->mark_as_flash(is_array($data) ? array_keys($data) : $data);
	}
}

?>