<?php
$LAVA =& get_instance();
$LAVA->load->helper('session');
$this->session->set(array('Hola' => 'Chika'));
print_r($_SESSION['SID']);
//$this->session->unset(array('Hola', 'SID'));

//$LAVA->input->
?>