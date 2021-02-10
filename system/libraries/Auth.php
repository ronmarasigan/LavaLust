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
 * @version Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
 * ------------------------------------------------------
 *  Authentication
 * ------------------------------------------------------
 */
class Auth {

	private $LAVA;

	public function __construct() {
		$this->LAVA =& get_instance();
		$this->LAVA->load->database();
		$this->LAVA->load->library('session');
	}

	/**
	 * Password Default Hash
	 * @param  string $password User Password
	 * @return string  Hashed Password
	 */
	public function passwordhash($password)
	{
		$options = array(
		'cost' => 4,
		);
		return password_hash($password, PASSWORD_BCRYPT, $options);
	}

	/**
	 * [register description]
	 * @param  string $username  Username
	 * @param  string $password  Password
	 * @param  string $email     Email
	 * @param  string $usertype   Usertype
	 * @return $this
	 */
	public function register($username, $password, $email, $usertype)
	{
		$bind = array(
			'username' => $username,
			'password' => $this->passwordhash($password),
			'email' => $email,
			'usertype' => $usertype,
			);
		return $this->LAVA->db->table('user')
						->insert($bind)
						->exec();
	}

	/**
	 * Login
	 * @param  string $username Username
	 * @param  string $password Password
	 * @return string Validated Username
	 */
	public function login($username, $password)
	{
    	$row = $this->LAVA->db->table('user') 					
    					->where('username', $username)
    					->get();
		if($row)
		{
			if(password_verify($password, $row['password']))
			{
				return $row['username'];
			} else {
				return false;
			}
		}
	}

	/**
	 * Set up session for login
	 * @param $this
	 */
	public function set_logged_in($username) {
		return $this->LAVA->session->set_userdata(array('username' => $username, 'loggedin' => 1));
	}

	/**
	 * Check if user is Logged in
	 * @return bool TRUE is logged in
	 */
	public function is_logged_in()
	{
		if($this->LAVA->session->userdata('loggedin') === 1)
			return true;
	}

	/**
	 * Get User ID
	 * @return string User ID from Session
	 */
	public function get_username()
	{
		$username = $this->LAVA->session->userdata('username');
		return !empty($username) ? $username : false;
	}

	public function set_logged_out() {
		$this->LAVA->session->unset_userdata(array('loggedin', 'username'));
		$this->LAVA->session->sess_destroy();
	}
}

?>