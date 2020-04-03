<!doctype html>
<html>
 <head>
   <title>How to send AJAX request in Codeigniter</title>
 </head>
 <body>
 
  Select Username : <select id='sel_user'>
     <?php 
     foreach($users as $user){
	echo "<option value='".$user['username']."' >".$user['username']."</option>";
     }
     ?>
  </select>

  <!-- User details -->
  <div >
   Username : <span id='username'></span><br/>
   Password : <span id='password'></span><br/>
  </div>

  <!-- Script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script type='text/javascript'>
  $(document).ready(function(){
 
   $('#sel_user').change(function(){
    var username = $(this).val();
    //alert(username);

    $.ajax({
	   type: 'POST',
	   url:'<?php echo BASE_URL . site_url('Welcome/userDetails'); ?>',
	   method: 'post',
	   data: {username: username},
	   dataType: 'json',
	   success: function( response ) {

       	 	var username = response.username;
         	var password = response.password;
	   		$('#username').text(username);
         	$('#password').text(password);
         
	   }
	   
	});


   
  });
 });
 </script>
 </body>
</html>