<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 * 
 * Copyright (c) 2020 Ronald M. Marasigan
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @copyright Copyright 2020 (https://ronmarasigan.github.io)
 * @since Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>PHP Error Occured</title>
	<style type="text/css">
		html {
		    height: 100%;
		}

		body{
		    color: #888;
		    margin: 10px;
		    box-shadow: rgba(0, 0, 0, 0.16) 0px 10px 36px 0px, rgba(0, 0, 0, 0.06) 0px 0px 0px 1px;
		}

		.border{
			border: 1px solid #990000;
			padding: 10px;
		}

		.header {
			font-size: 20px;
			background-color: #AB5C4B;
			color: #ffffff;
			padding: 5px;
			
		}

		.stack_trace {
			color: #000000;
			background-color: #fff1f1;
			padding: 5px;
		}

		.err_body {
			color: #000000;
			background-color: #ffffff;
			padding: 5px;
		}
	</style>
</head>
<body>
	<div class="header"><h4>A PHP Error was encountered</h4></div>
		<div class="err_body">
			<p>Severity: <?php echo $severity; ?></p>
			<p>Message:  <?php echo $message; ?></p>
			<p>Filename: <?php echo $filepath; ?></p>
			<p>Line Number: <?php echo $line; ?></p>
		</div>
		<div class="stack_trace">
			<p style="font-weight: bold">Stack trace:</p>
			<?php foreach (debug_backtrace() as $error): ?>
				<?php if (isset($error['file']) && strpos($error['file'], realpath(SYSTEM_DIR)) !== 0): ?>
					<p style="margin-left:10px">
					File: <?php echo $error['file']; ?><br />
					Line: <?php echo $error['line']; ?><br />
					Function: <?php echo $error['function']; ?>
					</p>
				<?php endif ?>
			<?php endforeach ?>
		</div>
</body>
</html>