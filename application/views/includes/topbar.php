<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!--top-Header-menu-->
<div id="user-nav" class="navbar navbar-inverse">
  <ul class="nav">
    <li class=""><a title="" href=""><i class="icon icon-magic"></i> <span class="text">Welcome user: <?php echo xss_clean($this->session->sessionID()); ?></span></a></li>
    <li class="dropdown" id="menu-language"><a href="#" data-toggle="dropdown" data-target="#menu-lang-en" class="dropdown-toggle"><i class="icon icon-flag"></i> <span class="text">Language</span></a>
      <ul class="dropdown-menu">
        <li><a class="lang_en" title="" href="<?php echo BASE_URL . 'en/'; ?>"> English</a></li>
        <li class="divider"></li>
        <li><a class="lang_tagalog" title="" href="<?php echo BASE_URL . 'tag/'; ?>"> Tagalog</a></li>
      </ul>
    </li>
    <li class="">
		<a title="" href="#">
			<i class="icon icon-leaf"></i> <span class="text"> <?php echo round(memory_get_usage() / 1024 / 1024, 2) ;?> MB</span>
			<i class="icon icon-beaker"></i> <span class="text"> 1.0.0</span>
		</a>
	</li>
  </ul>
</div>
<!--close-top-Header-menu-->
