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
}
?>
