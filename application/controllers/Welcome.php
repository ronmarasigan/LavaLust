<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller{

	public function index() {
		$this->load->view('welcome_page');
	}

	public function lan() {
		// //$this->lang->language('pt-BR');
		// echo $this->lang->translate('presentation');
		// echo $this->lang->translate('user.login.message', ['name' => 'John Doe', 'email' => 'john@doe.com']);
		$this->load->helper('language');
		echo lang('welcome_message');
	}

}