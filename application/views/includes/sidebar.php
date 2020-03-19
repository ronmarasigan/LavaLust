<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!--sidebar-menu-->
<div id="sidebar"><a href="#" class="visible-phone"><i class="icon icon-home"></i> Dashboard</a>
  <ul>
    <li class="<?php active('Case-in-PH') ;?>"><a href="<?php echo BASE_URL . site_url('COVID-19/Case-in-PH'); ?>"><i class="icon icon-list"></i> <span>COVID-19 Cases in PH</span></a></li>
    <li class="<?php active('Case-out-PH') ;?>"><a href="<?php echo BASE_URL . site_url('COVID-19/Case-out-PH'); ?>"><i class="icon icon-list"></i> <span>COVID-19 Cases outside PH</span></a></li>
    <li class="<?php active('Suspected-Cases') ;?>"><a href="<?php echo BASE_URL . site_url('COVID-19/Suspected-Cases'); ?>"><i class="icon icon-list-ol"></i> <span>Suspected Cases</span></a></li>
    <li class="<?php active('Under-Observation-Cases') ;?>"><a href="<?php echo BASE_URL . site_url('COVID-19/Under-Observation-Cases'); ?>"><i class="icon icon-eye-open"></i> <span>Under Observation</span></a></li>
    <li class="<?php active('Checkpoints') ;?>"><a href="<?php echo BASE_URL . site_url('COVID-19/Checkpoints'); ?>"><i class="icon icon-map-marker"></i> <span>Checkpoints (Metro Manila)</span></a></li>
     <li class="<?php active('Sources') ;?>"><a href="<?php echo BASE_URL . site_url('COVID-19/Information-Sources'); ?>"><i class="icon icon-info-sign"></i> <span>Sources</span></a></li>
  </ul>
</div>
<!--sidebar-menu-->
