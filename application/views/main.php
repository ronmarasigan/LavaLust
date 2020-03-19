<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/****
	*	BABAERON - a basic PHP MVC Framework is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation, either version 3 of the License, or
	* (at your option) any later version.

	* BABAERON - a basic PHP MVC Framework is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	* GNU General Public License for more details.

	* You should have received a copy of the GNU General Public License
	* along with BABAERON - a basic PHP MVC Framework.  If not, see <https://www.gnu.org/licenses/>.
	* 
	* @author 		Ronald M. Marasigan
	* @copyright	Copyright (c) 2018, BABAERON - a basic PHP Framework
	* @license		https://www.gnu.org/licenses	GNU General Public License V3.0
	* @link		https://github.com/BABAERON/Babaeron-PHP-MVC-Framework
****/

require_once('includes/header.php'); ?>
<!--Header-part-->
<div id="header">
  <h1><a href="#"></a></h1>
</div>
<!--close-Header-part--> 

<!-- includes/Include topbar -->
<?php require_once('includes/topbar.php'); ?>
<!-- end includes/Include -->

<!-- includes/Include sidebar -->
<?php require_once('includes/sidebar.php'); ?>
<!-- end includes/Include -->

<!--main-container-part-->
<div id="content">
<!--breadcrumbs-->
  <div id="content-header">
    <div id="breadcrumb"></div>
      <?php if(isset($sources)): ?>
      <h1><?php echo LANG['Sources'] ;?></h1>
      <?php endif; ?>
      <?php if(isset($results)): ?>
      <h1><?php echo LANG['Summary of COVID-19 cases in the Philippines'] ;?></h1>
      <?php endif; ?>
      <?php if(isset($results_out)): ?>
      <h1><?php echo LANG['Confirmed cases of Filipino nationals outside the Philippines'] ;?></h1>
      <?php endif; ?>
      <?php if(isset($results_suspected)): ?>
      <h1><?php echo LANG['Number of Suspected Cases'] ;?></h1>
      <?php endif; ?>
      <?php if(isset($results_observation)): ?>
      <h1><?php echo LANG['Cases Under Observation'] ;?></h1>
      <?php endif; ?>
      <?php if(isset($results_checkpoints)): ?>
      <h1>
      <h1><?php echo LANG['Checkpoints / Metro Manila'] ;?></h1>
      <?php endif; ?>
  </div>
    <div class="container-fluid">
      <hr>
    <?php
      if(isset($sources))
      { ?>
      <div class="row-fluid">
        <div class="span12">
          <div class="widget-box">
          <div class="widget-title"> <span class="icon"> <i class="icon-refresh"></i> </span>
            <h5>List</h5>
          </div>
          <div class="widget-content nopadding updates">
            <div class="new-update clearfix"><i class="icon-ok-sign"></i>
              <div class="update-done"><a href="https://github.com/sorxrob/coronavirus-ph-api"><strong>https://github.com/sorxrob/coronavirus-ph-api</strong></a><span>coronavirus-ph (API) by Robert C Soriano</span> </div>
            </div>
          </div>
          <div class="widget-content nopadding updates">
            <div class="new-update clearfix"><i class="icon-ok-sign"></i>
              <div class="update-done"><a href="https://en.wikipedia.org/wiki/2020_coronavirus_pandemic_in_the_Philippines"><strong>Wikipedia</strong></a> </div>
            </div>
          </div>
          <div class="widget-content nopadding updates">
            <div class="new-update clearfix"><i class="icon-ok-sign"></i>
              <div class="update-done"><a href="https://www.reddit.com/r/Coronavirus_PH/comments/fehzke/ph_covid19_case_database_is_now_live/"><strong>Cases from r/Coronavirus_PH spreadsheet</strong></a> </div>
            </div>
          </div>
          <div class="widget-content nopadding updates">
            <div class="new-update clearfix"><i class="icon-ok-sign"></i>
              <div class="update-done"><a href="https://safetravel.ph/"><strong>Metro Manila community quarantine checkpoints</strong></a> </div>
            </div>
          </div>
        </div>
      </div>
      </div>
      <?php
      }
      if(isset($results))
        { ?>
          <div class="row-fluid">
            <div class="span12">
              <div class="widget-box">
                <div class="widget-title"> <span class="icon"><i class="icon-th"></i></span>
                  <h5>Lists</h5>
                </div>
                <div class="widget-content nopadding">
                  <table class="table table-bordered data-table">
                    <thead>
                      <tr>
                        <th>Case No.</th>
                        <th>Date</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Nationality</th>
                        <th>Hospital Admitted To</th>
                        <th>Travel History</th>
                        <th>Status</th>
                        <th>Other Information</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($results as $result): ?>
                      <tr class="gradeA">
                        <td><?php echo xss_clean($result->case_no); ?></td>
                        <td><?php echo xss_clean($result->date); ?></td>
                        <td><?php echo xss_clean($result->age); ?></td>
                        <td><?php echo xss_clean($result->gender); ?></td>
                        <td><?php echo xss_clean($result->nationality); ?></td>
                        <td><?php echo xss_clean($result->hospital_admitted_to); ?></td>
                        <td><?php echo xss_clean($result->had_recent_travel_history_abroad); ?></td>
                        <td><?php echo xss_clean($result->status); ?></td>
                        <td><?php echo xss_clean($result->other_information); ?></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php
        }
        if(isset($results_out))
        { ?>
          <div class="row-fluid">
            <div class="span12">
              <div class="widget-box">
                <div class="widget-title"> <span class="icon"><i class="icon-th"></i></span>
                  <h5>Lists</h5>
                </div>
                <div class="widget-content nopadding">
                  <table class="table table-bordered data-table">
                    <thead>
                      <tr>
                        <th>Country/Teritory/Place</th>
                        <th>Confirmed</th>
                        <th>Recovered</th>
                        <th>Died</th>               
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($results_out as $result): ?>
                      <?php if($result->country_territory_place != 'Total'): ?>
                      <tr class="gradeA">
                        <td><?php echo xss_clean($result->country_territory_place); ?></td>
                        <td><?php echo xss_clean($result->confirmed); ?></td>
                        <td><?php echo xss_clean($result->recovered); ?></td>
                        <td><?php echo xss_clean($result->died); ?></td>                      
                      </tr>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php
        }
        if(isset($results_suspected))
        { ?>
          <div class="row-fluid">
            <div class="span12">
              <div class="widget-box">
                <div class="widget-title"> <span class="icon"><i class="icon-th"></i></span>
                  <h5>Lists</h5>
                </div>
                <div class="widget-content nopadding">
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Confirmed Cases</th>
                        <th>Tested Negative</th>
                        <th>Pending Test Results</th>            
                      </tr>
                    </thead>
                    <tbody>
                      <tr class="gradeA">
                      <?php foreach($results_suspected as $key => $value): ?>                    
                        <td style="text-align:center"><?php echo xss_clean($key=$value); ?></td>
                      <?php endforeach; ?>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php
        }
        if(isset($results_observation))
        { ?>
          <div class="row-fluid">
            <div class="span12">
              <div class="widget-box">
                <div class="widget-title"> <span class="icon"><i class="icon-th"></i></span>
                  <h5>Lists</h5>
                </div>
                <div class="widget-content nopadding">
                  <table class="table table-bordered data-table">
                    <thead>
                      <tr>
                        <th>Region</th>
                        <th>Suspected Cases/Admitted</th>
                        <th>Suspected Cases/Deaths</th>
                        <th>Confirmed Cases/Admitted</th>   
                        <th>Confirmed Cases/Recoveries</th>   
                        <th>Confirmed Cases/Deaths</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($results_observation as $result): ?>
                      <tr class="gradeA">
                        <td><?php echo xss_clean($result->region); ?></td>
                        <td><?php echo xss_clean($result->current_pui_status->suspected_cases->admitted); ?></td>
                        <td><?php echo xss_clean($result->current_pui_status->suspected_cases->deaths); ?></td>
                        <td><?php echo xss_clean($result->current_pui_status->confirmed_cases->admitted); ?></td>
                        <td><?php echo xss_clean($result->current_pui_status->confirmed_cases->recoveries); ?></td>
                        <td><?php echo xss_clean($result->current_pui_status->confirmed_cases->deaths); ?></td>  
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php
        }
        if(isset($results_checkpoints))
        { ?>
          <div class="row-fluid">
            <div class="span12">
              <div class="widget-box">
                <div class="widget-title"> <span class="icon"><i class="icon-th"></i></span>
                  <h5>Lists</h5>
                </div>
                <div class="widget-content nopadding">
                  <table class="table table-bordered data-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>District</th>
                        <th>City</th>
                        <th>Location</th>   
                        <th>Type</th> 
                        <th>Description</th>   
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($results_checkpoints as $result): ?>
                      <tr class="gradeA">
                        <td><?php echo xss_clean($result->id); ?></td>
                        <td><?php echo xss_clean($result->district); ?></td>
                        <td><?php echo xss_clean($result->city); ?></td>
                        <td><?php echo xss_clean($result->location); ?></td>
                        <td><?php echo xss_clean($result->type); ?></td>  
                        <td><?php echo xss_clean($result->description); ?></td>  
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php
        }
        ?>
    </div>
</div>

<!--end-main-container-part-->

<!-- includes/Include footbar -->
<?php require_once('includes/footbar.php'); ?>
<!-- end includes/Include -->

<?php load_js(array('jquery.min', 'jquery.ui.custom', 'bootstrap.min', 'jquery.uniform', 'jquery.dataTables.min', 'matrix', 'matrix.tables', 'select2.min')); ?>

<script type="text/javascript">
  // This function is called from the pop-up menus to transfer to
  // a different page. Ignore if the value returned is a null string:
  function goPage (newURL) {

      // if url is empty, skip the menu dividers and reset the menu selection to default
      if (newURL != "") {
      
          // if url is "-", it is this page -- reset the menu:
          if (newURL == "-" ) {
              resetMenu();            
          } 
          // else, send page to designated URL            
          else {  
            document.location.href = newURL;
          }
      }
  }

// resets the menu selection upon entry to this page:
function resetMenu() {
   document.gomenu.selector.selectedIndex = 2;
}
</script>
<?php require_once('includes/footer.php'); ?>