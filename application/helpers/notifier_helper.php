<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');


function SQLError()
{
	return 'SQL Error: There is an error while executing the SQL statement.';
}

/***** SQL Result message *****/
function SQLResult($entity, $action)
{
	return '' . $entity . ' was successfully ' . $action . '';
}

/***** CSRFProtect Error message *****/
function CSRFTokenError()
{
	return 'CSRF Token Error: There is an error in CSRF token validation.';
}


?>