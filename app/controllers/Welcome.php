<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller {
	public function index() {
		//$this->call->view('welcome_page');
		echo site_url('welcome/index');
		load_js(['ass', 'sss']);
	}
}
?>