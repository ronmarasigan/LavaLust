<?php
$LAVA =& get_instance();
$LAVA->load->helper(array('session'));
echo $this->session->sessionId();

//$LAVA->input->
?>