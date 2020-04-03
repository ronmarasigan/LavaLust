<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
	class Sample_model extends Model{
		public function __construct()
		{
			parent::__construct();
			$this->db = new Model();
		}

		function select1()
		{
			$data = $this->db->select('users');
			return $data;
		}

		function getUserDetails($postData){

	 		$sql = 'select * from users where username = :username';
			$bind = array(
				':username' => $postData
			);
			$response = $this->db->fetchRow($sql, $bind);
		    
		 
		    return $response;
		}

	}
?>