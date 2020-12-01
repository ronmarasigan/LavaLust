<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class User extends Controller{

    public function index() {
	    $this->load->library('form_validation');
	    if($this->form_validation->submitted()) {
			if($this->form_validation->run()) {
				$this->load->library('auth');
				$username = $this->auth->login($this->io->post('username'), $this->io->post('password'));
				if(!empty($username)) {
					echo $username;
				}
			}
		}
		
		$this->load->view('test');
    }
}