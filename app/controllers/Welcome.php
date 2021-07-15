<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller {
	public function index() {
		$this->call->view('welcome_page');
	}

	public function s() {
		$this->call->library('session');
		$this->session->set_userdata(['ako '=> 'ron']);
	}
}
?>