<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends Controller {
	public function __construct() {
		parent:: __construct();
	}

	public function index()
	{
		$this->caseph();
	}

	public function caseph()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL,"https://coronavirus-ph-api.now.sh/cases");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$result = json_decode($response);
		curl_close ($ch);
		$this->load->view('main', array('results' => $result));
	}

	public function caseoutph()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL,"https://coronavirus-ph-api.now.sh/cases-outside-ph");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$result = json_decode($response);
		curl_close ($ch);
		$this->load->view('main', array('results_out' => $result));
	}

	public function suspectedcases()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL,"https://coronavirus-ph-api.now.sh/suspected-cases");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$result = json_decode($response);
		curl_close ($ch);
		$this->load->view('main', array('results_suspected' => $result));
	}

	public function underobservation()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL,"https://coronavirus-ph-api.now.sh/patients-under-investigation");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$result = json_decode($response);
		curl_close ($ch);
		$this->load->view('main', array('results_observation' => $result));
	}

	public function checkpoints()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL,"https://coronavirus-ph-api.now.sh/mm-checkpoints");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$result = json_decode($response);
		curl_close ($ch);
		$this->load->view('main', array('results_checkpoints' => $result));
	}

	public function sources()
	{
		$this->load->view('main', array('sources' => 1));
	}
}
?>