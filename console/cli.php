#!/usr/bin/php -q

<?php
(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die('cli only');
//Constant 
define('ROOT_DIR',  dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_DIR', ROOT_DIR . 'app' . DIRECTORY_SEPARATOR);
echo '-----------------------';
echo "\n";
echo 'CREATE A NEW FILE:';
echo "\n";
echo '-----------------------';
echo "\n";
echo 'Type M => Model';
echo "\n";
echo 'Type V => View';
echo "\n";
echo 'Type C => Controller';
echo "\n";
echo '-----------------------';
echo "\n";

$option = strtoupper(readline('File: '));
$flag = FALSE;
switch ($option) {
	case 'M':
		$option = 'Model';
		$flag = TRUE;
		break;
	case 'V':
		$option = 'View';
		$flag = TRUE;
		break;
	case 'C':
		$option = 'Controller';
		$flag = TRUE;
		break;
	default:
		echo 'Invalid input. Please choose from M, V, C';
		echo "\n";
		break;
}
if($flag) {
	$class = ucfirst(readline('Enter ' . $option . ' name: '));
	$_m = '';
	if(strtolower($option) == 'model') $_m = '_model';
	if(! file_exists(APP_DIR . strtolower($option) . 's\\' . $class . ''.$_m.'.php')) {
		$file_handle = fopen(APP_DIR . strtolower($option) . 's\\' . $class . ''.$_m.'.php', 'w');
		fwrite($file_handle, '<?php');
		fwrite($file_handle, "\n");
		fwrite($file_handle, "defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');");
		fwrite($file_handle, "\n");
		fwrite($file_handle, "class ".$class."".$_m." extends ".$option." {");
		fwrite($file_handle, "\n");
		fwrite($file_handle, "}");
		fwrite($file_handle, "\n");
		fwrite($file_handle, '?>');
		fclose($file_handle);
	} else {
		echo 'File aready exist.';
	}
}

?>