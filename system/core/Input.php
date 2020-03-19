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
* ------------------------------------------------------
*  Class Input
* ------------------------------------------------------
*/
Class Input {

	private $security;
    
	public function __construct()
  	{
    	global $Security;
    	$this->security = $Security;
  	}
  	/*
	* ------------------------------------------------------
	*  POST Variable
	* ------------------------------------------------------
	*/
	public function post($post = NULL, $xss_clean = FALSE)
	{
		if ( isset($_POST[$post]) ) {
			if ( $xss_clean === TRUE ) {
				return $this->security->xss_clean($_POST[$post]);		
			} else {
				return $_POST[$post];	
			}
		}
	}

	/*
	* ------------------------------------------------------
	*  GET Variable
	* ------------------------------------------------------
	*/
	public function get($get = NULL, $xss_clean = FALSE)
	{
		if ( isset($_GET[$get]) ) {
			if ( $xss_clean === TRUE ) {
				return $this->security->xss_clean($_GET[$get]);
			} else {
				return $_GET[$get];
			}
		}
	}

	/*
	* ------------------------------------------------------
	*  COOKIE Variable
	* ------------------------------------------------------
	*/
	public function cookie($cookie = NULL, $xss_clean = FALSE)
	{
		if ( isset($_COOKIE[$cookie]) ) {
			if ( $xss_clean === TRUE ) {
				return $this->security->xss_clean($_COOKIE[$cookie]);		
			} else {
				return $_COOKIE[$cookie];	
			}
		}
	}

	/*
	* ------------------------------------------------------
	*  Set Cookies
	* ------------------------------------------------------
	*/
	public function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL)
	{
		if (is_array($name))
		{
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
			{
				if (isset($name[$item]))
				{
				$$item = $name[$item];
				}
			}
		}

	global $config;

	if ($prefix === '' && $config['cookie_prefix'] !== '')
	{
		$prefix = $config['cookie_prefix'];
	}

	if ($domain == '' && $config['cookie_domain'] != '')
	{
		$domain = $config['cookie_domain'];
	}

	if ($path === '/' && $config['cookie_path'] !== '/')
	{
		$path = $config['cookie_path'];
	}

	$secure = ($secure === NULL && $config['cookie_secure'] !== NULL)
	? (bool) $config['cookie_secure']
	: (bool) $secure;

	$httponly = ($httponly === NULL && $config['cookie_httponly'] !== NULL)
	? (bool) $config['cookie_httponly']
	: (bool) $httponly;

	if ( ! is_numeric($expire))
	{
		$expire = time() - 86500;
	}
	else
	{
		$expire = ($expire > 0) ? time() + $expire : 0;
	}

	setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/*
	* ------------------------------------------------------
	*  IP Adress
	* ------------------------------------------------------
	*/
	public function ip_address() 
	{
		if ( filter_var($this->server('REMOTE_ADDR'), FILTER_VALIDATE_IP) !== FALSE ) {
			return $this->server('REMOTE_ADDR',TRUE);
		} else {
			return '0.0.0.0';
		}
	}

	/*
	* ------------------------------------------------------
	*  User Agent
	* ------------------------------------------------------
	*/
	public function user_agent()
	{
		return $this->server('HTTP_USER_AGENT',TRUE);
	}

	/*
	* ------------------------------------------------------
	*  Server
	* ------------------------------------------------------
	*/
	public function server($server = NULL, $xss_clean = FALSE)
	{
	if ( isset($_SERVER[$server]) ) {
		if ( $xss_clean === TRUE ) {
			return $this->security->xss_clean($_SERVER[$server]);
		} else {
			return $_SERVER[$server];
		}
	}
	}
}
	
?>