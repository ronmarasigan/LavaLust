<!DOCTYPE html>
<html>
<head>
	<title>Forms</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>
<body>
	<div class="container-fluid">
		<div class="row mt-5">
			<div class="col-sm-4">
				
				<?php
				if(validation_errors())
					echo '<div class="alert alert-danger">
					'.validation_errors().'</div>';
				?>
				<?=form_open();?>
				<div class="form-group">
					<?=form_input(array('name'=>'username', 'class'=>'form-control', 'placeholder'=>'Username'));?>
				</div>
				<div class="form-group">
					<?=form_input(array('type'=>'password', 'name'=>'password', 'class'=>'form-control', 'placeholder'=>'Password'));?>
				</div>
				<div class="form-group">
					<?=form_submit(array('name'=>'submit', 'class'=>'btn btn-primary'), 'Login');?>
				</div>
				<?=form_close();?>
			</div>
		</div>
	</div>
</body>
</html>