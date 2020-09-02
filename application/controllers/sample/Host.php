<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Host extends Controller{

	public function __construct() {
		parent:: __construct();
		
	}

    public function index2()
    {
    	//$LAVA =& get_intance();
        $this->load->view('welcome_page');
    }

}
?>