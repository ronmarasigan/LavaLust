<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends Controller {

	private $security;
	private $result;

	function __construct()
	{
		parent:: __construct();
		global $Input;
		$this->input = $Input;

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
	    $postData = $this->input->post('username');
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

	function covid()
	{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_URL,"https://coronavirus-ph-api.now.sh/cases");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec ($ch);
	$result = json_decode($response);
	//return $this->result; 
	curl_close ($ch);

	//var_dump($result);
	$this->load->view('main3', array('result' => $result));
	}

	function try()
	{
		$this->load->view('covid');
	}
}
?>
