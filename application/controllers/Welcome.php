<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller{

	public function index()
	{
		$this->load->view('welcome_page');
		$this->benchmark->mark('total_execution_time_end');
	}

	public function test()
	{
		$this->load->library('form', array('bootstrap'));
		//$this->config->load('database');
		//echo $this->session->get_userdata('username');
		$data['title'] = 'hu u';
		$data2['header'] = 'header';
		//$this->load->view('form', array('data' => $data, 'data2' => $data2));
		$this->load->view('form', $data);
	}

	public function test2()
	{
		// $this->load->helper('security');
		// $this->load->library('escaper');
		// $this->load->library('session');
		// // //$this->auth->register('acidcore', '12', 'email@email.com', 'admin');
		
		// // $data = $this->auth->login('acidcore', '12');
		
		// // $this->session->set_userdata(array('username' => $data['username'], 'loggedin' => true));
		// echo html_escape($this->io->post('name'));
		// echo xss_clean('<script>alert("xd")</script>');
		// var_dump(is_loaded());
	}

}
?>