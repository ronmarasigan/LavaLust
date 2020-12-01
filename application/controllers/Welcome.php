<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller{
	public function __construct() {
		parent::__construct();
	}

	public function index() {
		//$this->load->view('welcome_page');
		$this->load->model('sample');
		var_dump($this->sample->all_users());
	}

	public function form() {
		$this->load->library('form_validation');
		if($this->form_validation->submitted()) {
			$this->form_validation
				
				->name('password|Password')->min_length(8, 'Password needs more than 7 characters')
				->name('password2|Confirm Password')->min_length(8, 'Password needs more than 7 characters')->matches('password', 'Password and Confirm Password did not match')
				->name('email|Email')->valid_email('Please enter a valid email');
				if($this->form_validation->run()) {
					$this->load->library('auth');
					if($this->auth->register($this->io->post('username'), $this->io->post('password'), $this->io->post('email'), 'admin')) {
						echo 'Success';
					}
				}
		}
		$this->load->view('form');
	}

	public function loaded() {
		// $db = $this->load->database();
		// $data = $db->table('user')->where('id', 3)->get();
		// //foreach($data as $datum)
		// 	echo $data['username'];
		var_dump(is_loaded());
		
	}

	public function sendmail() {
		$this->load->library('email');
		$this->email->subject('Example mail');
		$this->email->sender('john.doe@example.com');

		//Set the plain content of the mail.
		$this->email->email_content('
									<html>
									<head>
									<title>HTML email</title>
									</head>
									<body>
									<p>This email contains HTML Tags!</p>
									<table>
									<tr>
									<th>Firstname</th>
									<th>Lastname</th>
									</tr>
									<tr>
									<td>John</td>
									<td>Doe</td>
									</tr>
									</table>
									</body>
									</html>
									', 'html');
		$this->email->attachment('C:\wamp64\www\lavalust\readme.txt');
		//Add a receiver of the mail (you can add more than one receiver too).
		$this->email->recipient('mike.doe@example.com');

		//Finally send the mail.
		$this->email->send();
	}

	public function sess() {
		$this->session->set_userdata(['sess'=>'try']);
	}

	public function s() {
		if($this->session->has_userdata('sess')) {
			echo 'meron';
		}
	}

}