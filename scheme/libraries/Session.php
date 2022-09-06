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

/**
 * Class Session
 */
class Session {

	/**
	 * Config var to hold Config files
	 *
	 * @var mixed
	 */
	private $config;

	public function __construct()
	{
		/**
		 * Session Configs
		 */
		$this->config =& get_config();
		
		//IP Matching
		$this->match_ip = $this->config['sess_match_ip'];

		//Fingerprint Matching
        $this->match_fingerprint = $this->config['sess_match_fingerprint'];

		//Set up cookie name
		if ( ! empty($this->config['cookie_prefix']) ) {
	    	$this->config['cookie_name'] = $this->config['sess_cookie_name'] ? $this->config['cookie_prefix'].$this->config['sess_cookie_name'] : NULL;
	    } else {
	    	$this->config['cookie_name'] = $this->config['sess_cookie_name'] ? $this->config['sess_cookie_name'] : NULL;
	    }
		
		//Set up cookie name
	    if (empty($this->config['cookie_name']))
		{
	    	$this->config['cookie_name'] = ini_get('session.name');
	    } else {
	    	ini_set('session.name', $this->config['cookie_name']);
	    }

		//Set up session expiration
	    if (empty($this->config['sess_expiration']))
		{
	    	$this->config['sess_expiration'] = (int) ini_get('session.gc_maxlifetime');
	    } else {
	    	$this->config['sess_expiration'] = (int) $this->config['sess_expiration'];
	    	ini_set('session.gc_maxlifetime', $this->config['sess_expiration']);
	    }

	    if (isset($this->config['cookie_expiration']))
		{
	    	$this->config['cookie_expiration'] = (int) $this->config['cookie_expiration'];
		} else {
	    	$this->config['cookie_expiration'] = ( ! isset($this->config['sess_expiration']) AND $this->config['sess_expire_on_close']) ? 0 : (int) $this->config['sess_expiration'];
		}
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
		} elseif ( ! empty($this->config['sess_driver']) AND $this->config['sess_driver'] == 'database' ) {
			
		}

	    //On creation store the useragent fingerprint
		if(empty($_SESSION['fingerprint']))
		{
			$_SESSION['fingerprint'] = $this->generate_fingerprint();
		//If we should verify user agent fingerprints (and this one doesn't match!)
		} elseif($this->match_fingerprint && $_SESSION['fingerprint'] != $this->generate_fingerprint()) {
			return FALSE;
		}

		//If an IP address is present and we should check to see if it matches
		if(isset($_SESSION['ip_address']) && $this->match_ip)
		{
			//If the IP does NOT match
			if($_SESSION['ip_address'] != $_SERVER['REMOTE_ADDR'])
			{
				return FALSE;
			}
		}

		//Set the users IP Address
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

	    if ( isset($_COOKIE[$this->config['cookie_name']]) )
		{
	    	preg_match('/('.session_id().')/', $_COOKIE[$this->config['cookie_name']], $matches);
	    	if ( empty($matches) )
			{
	        	unset($_COOKIE[$this->config['cookie_name']]);
	      	}
	    }

		session_start();

		//Set time before session updates
	    $regenerate_time = (int) $this->config['sess_time_to_update'];

		//Check for Ajax
	    if ( (empty($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') AND ($regenerate_time > 0) )
		{
	    	if ( ! isset($_SESSION['last_session_regenerate']))
			{
	        	$_SESSION['last_session_regenerate'] = time();
	    	} elseif ( $_SESSION['last_session_regenerate'] < (time() - $regenerate_time) ) {
		        $this->sess_regenerate((bool) $this->config['sess_regenerate_destroy']);
	      	}
	    } elseif (isset($_COOKIE[$this->config['cookie_name']]) AND $_COOKIE[$this->config['cookie_name']] === $this->session_id()){
			//Check for expiration time
			$expiration = empty($this->config['cookie_expiration']) ? 0 : time() + $this->config['cookie_expiration'];

			setcookie(
				$this->config['cookie_name'],
				$this->session_id(),
				array('samesite' => $this->config['cookie_samesite'],
				'secure'   => $this->config['cookie_secure'],
				'expires'  => $expiration,
				'path'     => $this->config['cookie_path'],
				'domain'   => $this->config['cookie_domain'],
				'httponly' => $this->config['cookie_httponly'],
				)
			);
	    }

	    $this->_lava_init_vars();
	}

	/**
	 * Generates key as protection against Session Hijacking & Fixation. This
	 * works better than IP based checking for most sites due to constant user
	 * IP changes (although this method is not as secure as IP checks).
	 * @return string
	 */
	public function generate_fingerprint()
	{
		//We don't use the ip-adress, because it is subject to change in most cases
		foreach(array('ACCEPT_CHARSET', 'ACCEPT_ENCODING', 'ACCEPT_LANGUAGE', 'USER_AGENT') as $name) {
			$key[] = empty($_SERVER['HTTP_'. $name]) ? NULL : $_SERVER['HTTP_'. $name];
		}
		//Create an MD5 has and return it
		return md5(implode("\0", $key));
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
				elseif ($value === 'old' || $value < $current_time)
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
	 * SID length
	 * 
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
	 * 
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
	 * 
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
	 * Keep flash data
	 *
	 * @param mixed $key
	 * @return void
	 */
	public function keep_flashdata($key)
	{
		$this->mark_as_flash($key);
	}
   	
   	/**
   	 * Return Session ID
   	 * @return string Session ID
   	 */
	public function session_id()
	{
		$seed = str_split('abcdefghijklmnopqrstuvwxyz0123456789');
        $rand_id = '';
        shuffle($seed);
        foreach (array_rand($seed, 32) as $k)
		{
            $rand_id .= $seed[$k];
        }
        return $rand_id; 
	}

	/**
	 * Check if session variable has data
	 * 
	 * @param  string $key Session
	 * @return boolean
	 */
	public function has_userdata($key = null)
	{
		if(! is_null($key))
		{
			if(isset($_SESSION[$key]))
			{
				return TRUE;
			}	
		}
		return FALSE;
	}
	
	/**
	 * Set Data to Session Key
	 * 
	 * @param array $keys array of Sessions
	 */
	public function set_userdata($keys, $value = NULL)
	{
		if(is_array($keys))
		{
			foreach($keys as $key => $val)
			{
				$_SESSION[$key] = $val;
			}
		} else {
			$_SESSION[$keys] = $value;
		}
	}
	
	/**
	 * Unset Session Data
	 * 
	 * @param  array  $keys Array of Sessions
	 * @return function
	 */
	public function unset_userdata($keys)
	{
		if(is_array($keys))
		{
			foreach ($keys as $key)
			{
				if($this->has_userdata($key))
				{
					unset($_SESSION[$key]);
				}
			}
		} else {
			if($this->has_userdata($keys))
			{
				unset($_SESSION[$keys]);
			}
		}
	}

	/**
	 * Get Flash Keys
	 *
	 * @return void
	 */
	public function get_flash_keys()
	{
		if ( ! isset($_SESSION['__lava_vars']))
		{
			return array();
		}

		$keys = array();
		foreach (array_keys($_SESSION['__lava_vars']) as $key)
		{
			is_int($_SESSION['__lava_vars'][$key]) OR $keys[] = $key;
		}

		return $keys;
	}
	
	/**
	 * Unmark Flash keys
	 *
	 * @param mixed $key
	 * @return void
	 */
	public function unmark_flash($key)
	{
		if (empty($_SESSION['__ci_vars']))
		{
			return;
		}

		is_array($key) OR $key = array($key);

		foreach ($key as $k)
		{
			if (isset($_SESSION['__ci_vars'][$k]) && ! is_int($_SESSION['__ci_vars'][$k]))
			{
				unset($_SESSION['__ci_vars'][$k]);
			}
		}

		if (empty($_SESSION['__ci_vars']))
		{
			unset($_SESSION['__ci_vars']);
		}
	}
	
   	/**
   	 * Get specific session key value

   	 * @param  array $key Session Keys
   	 * @return string      Session Data
   	 */
	public function userdata($key = NULL)
	{
		if(isset($key))
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
		}
		elseif (empty($_SESSION))
		{
			return array();
		}
		$userdata = array();
		$_exclude = array_merge(
			array('__lava_vars'),
			$this->get_flash_keys(),
		);

		foreach (array_keys($_SESSION) as $key)
		{
			if ( ! in_array($key, $_exclude, TRUE))
			{
				$userdata[$key] = $_SESSION[$key];
			}
		}

		return $userdata;
	}
	
	/**
	 * Session Destroy
	 * 
	 * @return function
	 */
	public function sess_destroy()
	{
		session_destroy();
	}

	/**
	 * Get flash data to Session
	 * 
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
	 * Set flash data to Session
	 * 
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