<!DOCTYPE html>
<html>
<head>
	<title>Forms</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>
<body>
	<div class="container-fluid">
		<?php
			$this->load->library('form', array('bootstrap'));
			echo $this->form->create_form('Name, Email, Comments|textarea', site_url('welcome/test2'));
		?>
	</div>
</body>
</html>