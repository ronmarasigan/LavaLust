<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller {
	public function index() {
		$this->call->view('welcome_page');
	}

	public function t() {
		$this->call->database();

		var_dump($this->db->table('member')->where('id', 9)->update(array('username'=>'ronron')));
	}
}
?>