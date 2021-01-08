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
 * @version Version 1.3.4
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
 * ------------------------------------------------------
 *  Authentication
 * ------------------------------------------------------
 */
class Auth {

	private $db;

	public function __construct() {
		$this->db = get_instance()->load->database();
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
	 * @return array            [description]
	 */
	public function register($username, $password, $email, $usertype)
	{
		$bind = array(
			'username' => $username,
			'password' => $this->passwordhash($password),
			'email' => $email,
			'usertype' => $usertype,
			);
		return $this->db->table('user')
						->insert($bind)
						->exec();
	}

	/**
	 * Login
	 * @param  string $username Username
	 * @param  string $password Password
	 * @return string           Validated Username
	 */
	public function login($username, $password)
	{
    	$row = $this->db->table('user') 					
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
	 * Check if user is Logged in
	 * @return bool TRUE is logged in
	 */
	public function loggedin()
	{
		if(get_instance()->session->get_userdata('loggedin') === 1)
			return true;
	}

	/**
	 * Get User ID
	 * @return string User ID from Session
	 */
	public function user_id()
	{
		$user_id = get_instance()->session->get_userdata('user_id');
		return !empty($user_id) ? $user_id : false;
	}
}

?>