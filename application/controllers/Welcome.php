<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller{

	public function index() {
		//$this->load->view('welcome_page');
		//$this->load->library('database');
		$this->load->database();
		var_dump($this->db->table('comments')->getAll());
	}

	public function sa() {
		var_dump($this->db->table('comments')->getAll());
	}
}