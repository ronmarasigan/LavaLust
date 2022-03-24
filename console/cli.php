#!/usr/bin/php -q
<?php
(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die('cli only');
//Constant 
define('ROOT_DIR',  dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_DIR', ROOT_DIR . 'app' . DIRECTORY_SEPARATOR);
$flag = FALSE;
do
{
echo '
------------------------------------------------------------
CREATE A NEW FILE:
------------------------------------------------------------
Type M => Model
Type C => Controller
------------------------------------------------------------
';
$option = strtoupper(readline('File: '));
switch ($option)
{
	case 'M':
		$option = 'Model';
		$flag = TRUE;
		break;
	case 'C':
		$option = 'Controller';
		$flag = TRUE;
		break;
	default:
		echo 'Invalid input. Please choose from M, C';
		$flag = FALSE;
		echo "\n";
		break;
}
$content = "<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class {class} extends {extends} {
	
}
?>
";
if($flag)
{
	$class = ucfirst(readline('Enter ' . $option . ' name: '));
	$_m = '';
	if(strtolower($option) == 'model')
	{
		$_m = '_model';
	}
	if(! file_exists(APP_DIR . strtolower($option) . 's\\' . $class . ''.$_m.'.php'))
	{
		$file_handle = fopen(APP_DIR . strtolower($option) . 's\\' . $class . ''.$_m.'.php', 'w');
		$search = array('{class}', '{extends}');
		$replace = array($class.$_m, $option);
		$content = str_replace($search, $replace, $content);
		fwrite($file_handle, $content);
		fclose($file_handle);
		echo success($option .  ' was successfully created');
	} else {
		echo danger($option . ' already exist.');
	}
	$continue = readline("\nDo you want to continue? [Y/N]: ");
}
} while(strtoupper($continue) == 'Y');

function danger($string = '', $padding = true)
{
$length = strlen($string) + 4;
$output = '';

if ($padding)
{
	$output .= "\e[0;41m".str_pad(' ', $length, " ", STR_PAD_LEFT)."\e[0m".PHP_EOL;
}
$output .= "\e[0;41m".($padding ? '  ' : '').$string.($padding ? '  ' : '')."\e[0m".PHP_EOL;
if ($padding) 
{
	$output .= "\e[0;41m".str_pad(' ', $length, " ", STR_PAD_LEFT)."\e[0m".PHP_EOL;
}

return $output;
}

function success($string = '')
{
	return "\e[0;32m".$string."\e[0m";
}

?>