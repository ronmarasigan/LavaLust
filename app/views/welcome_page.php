<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Welcome to LavaLust</title>
    <link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">
    <style type="text/css">
        html {
            margin: 20px;
        }

        body{
            font-size: 15px;
            font-family: Tahoma, sans-serif;
            color: #888;
            box-shadow: rgba(0, 0, 0, 0.16) 0px 10px 36px 0px, rgba(0, 0, 0, 0.06) 0px 0px 0px 1px;
        }

        a {
            color: #003399;
            background-color: transparent;
            font-weight: normal;
        }

        code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 12px;
            background-color: #f9f9f9;
            border: 1px solid #D0D0D0;
            color: #002166;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }

        .header {
            font-size: 30px;
            background-color: #2980B9;
            color: #ffffff;
            padding: 15px;
            
        }

        .main {
            color: #000000;
            background-color: #ffffff;
            padding: 30px;
        }

        .footer {
            font-family: 'Courier New', monospace;
            color: #000000;
            background-color: #ffffff;
            padding: 5px;
            text-align: center;
            border-top: solid 1px #2980B9;
        }
    </style>
</head>
<body>
    <div class="header">LavaLust Framework</div>
    <div class="main">
        <b>LavaLust</b> is a <i>Lightweight PHP Framework</i> that uses MVC(Model View Controller) design pattern for people who are developing web applications using PHP. It helps you write code easily using Object-Oriented Approach. It also provides set of libraries for commonly needed tasks, as well as helper functions to minimize the amount of time coding.
        <br><br>
        <code>
            <b>System Requirements:</b>
            <ul>
                <li>At least use PHP 7.4 or higher</li>
                <li>MySQL 5 or higher</li>
                <li>PDO is installed</li>
                <li>Enaled mod_rewrite(optional but recommended for security purposes)</li>
            </ul>
        </code>
        <p>This view is located inside: </p>
        <code>app/views/welcome_page.php</code>

        <p>The corresponding controller for this view file: </p>
        <code>app/controllers/Welcome.php</code>

        <p>You can star and fork the <a href="https://github.com/ronmarasigan/LavaLust">Github Repository</a> and read its <a href="https://lavalust4.netlify.app/">Documentation</a>.</p>
    </div>
    <div class="footer">Page rendered with <?php echo $this->performance->memory_usage(); ?> in <strong><?php echo $this->performance->elapsed_time('lavalust'); ?></strong> seconds. <?php echo  (config_item('ENVIRONMENT') === 'development') ?  'LavaLust Version <strong>' . config_item('VERSION') . '</strong>' : '' ?>
    </div>

</body>
</html>