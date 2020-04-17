<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Welcome extends Controller {

	private $security;
	private $result;

	function __construct()
	{
		parent:: __construct();
		$this->load->helper('cookie');
		//global $Input;
		//$this->input = $Input;

		$this->load->model('sample');
		
	}
	function index()
	{
		$data = $this->sample->select1();
		$this->load->view('main', array('users' => $data));
	}
	function index1()
	{
		$this->load->view('main2');
	}

	function xd()
	{
		echo $this->input->post('name', TRUE);
	}

	public function second($a, $b, $c, $d, $e)
	{
		echo "$a, $b, $c, $d, $e";
	}

	

	public function userDetails(){
	    // POST data
	    $postData = $this->input->post('username', TRUE);
	    //echo $postData;
	    //die;
	    // get data
	    $data = $this->sample->getUserDetails($postData);

	    //print_r($data);
	    echo json_encode($data);
  	}

	function notifier()
	{
		$this->load->helper('notifier');
		echo xss_clean(SQLError());
	}

	function m3()
	{
		$this->load->view('main3');
	}

	function try()
	{
		$this->load->view('covid');
	}

	function vuesample()
	{
		$this->load->view('vue');
	}

	function cookie()
	{
		$cookie= array(

           'name'   => 'remember_me',
           'value'  => 'test',                            
           'expire' => '300',                                                                                   
           'secure' => TRUE

       );
		
		set_cookie($cookie);

       	//echo "Congratulation Cookie Set";
       	return $cookie;
	}

	function xss()
	{
		echo html_escape("<script>alert('xd')</script>");
	}

	function axios() {
		$this->load->view('axios');
	}

	function sendmail()
	{
		$this->load->library('email');
		$this->email->setSubject('Example mail');
		$this->email->setSender('john.doe@example.com');

		//Set the plain content of the mail.
		$this->email->setContentPlain('Example plain-content!');
		/*$contentHTML = '
			<html>
			    <head>
			        <title>Example mail</title>
			    </head>
			    <body>
			        <p style="çolor:red">This is the content of the example mail.</p>
			    </body>
			</html>';*/
		//$this->email->setContentHTML($contentHTML);
		$this->email->addAttachment('C:\wamp64\www\lavalust\readme.txt');
		//Add a receiver of the mail (you can add more than one receiver too).
		$this->email->addReceiver('mike.doe@example.com');

		//Finally send the mail.
		$this->email->send();
	}

	function mail()
	{
		$this->load->library('email');
		$this->email->setSubject('Example mail');
		$this->email->setSender('ronaldx@yahoo.com');
		
		$this->email->setEmailContent('Example plain-content!');
		$this->email->setAttachment('C:\wamp64\www\lavalust\readme.txt');
		$this->email->setRecipient('ronald@yahoo.com');
		
		$this->email->send();
	}

	function testparam()
	{
		$a = $arrayName = array('param' => 'test');
		$this->load->library('sample', $a);
		$this->sample->test();
	}

	function testparam1()
	{
		$this->load->library('sample');
		$this->load->library('sample1');
		$this->sample1->test1();
	}
}
?>
