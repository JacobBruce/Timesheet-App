<?php admin_valid(); 
	  
function date_sort($a, $b) {
	return $a['FinalDate'] - $b['FinalDate'];
}

function format_sheet($timesheet) {
	$result = array();
	if (!empty($timesheet) && $timesheet !== 'N/A') {
		while ($row = mysqli_fetch_assoc($timesheet)) {
			$row['FinalDate'] = strtotime(empty($row['WorkDate']) ? $row['Created'] : $row['WorkDate']);
			$result[] = $row;
		}
	}
	uasort($result, "date_sort");
	return $result;
}
	  
if (isset($_POST['hours_val'])) {
	$work_date = 0;
	if (isset($_POST['date_val'])) $work_date = $_POST['date_val'];
	if (empty($_POST['other_site'])) {
		$row_id = submit_hours($_GET['user'], $work_date, $_POST['hours_val'], $_POST['site_val']);
	} else {
		$row_id = submit_hours($_GET['user'], $work_date, $_POST['hours_val'], '', $_POST['other_site']);
	}
	if (empty($row_id)) {
		$error_msg = 'An unexpected error occurred, please try again later.';
	}
} elseif (isset($_GET['remove'])) {
	if (!remove_timesheet($_GET['remove'])) {
		$error_msg = 'An unexpected error occurred, please try again later.';
	}
}

if (isset($_GET['export'])) {	
	$account = mysqli_fetch_assoc(get_account_byid($_GET['user']));
	$days = empty($_GET['days']) ? 7 : $_GET['days'];
	$timesheet = get_hours($_GET['user'], $days);
	$sorted_ts = format_sheet($timesheet);
	
	if (!empty($sorted_ts)) {
		$csv_file = 'user_'.$_GET['user'].'_timesheet.csv';
		$days_txt = ($_GET['days'] === 'all') ? 'all' : $_GET['days']."_days";
		$total_hours = 0;
		$csv_data = "\"DATE\", \"SITE\", \"HOURS\"\n";
		
		foreach ($sorted_ts as $sts_key => $sts_val) {
			$hours = $sts_val['Hours'];
			$total_hours += $hours;
			$day = empty($sts_val['WorkDate']) ? $sts_val['Created'] : $sts_val['WorkDate'];
			$site = 'Unknown Site';
			if (empty($sts_val['OtherSite'])) {
				$site = get_site($sts_val['SiteID']);
				if (empty($site) || $site === 'N/A') {
					$site = 'Unknown Site';
				} else {
					$site = mysqli_fetch_assoc($site);
					$site = $site['SiteName'];
				}
			} else {
				$site = $sts_val['OtherSite'];
			}
			$day = str_replace(',', '-', $day);
			$site = str_replace(',', '-', $site);
			$hours = str_replace(',', '.', $hours);
			$csv_data .= "\"$day\", \"$site\", $hours\n";
		}
				
		$csv_data .= "\"TOTAL HOURS:\", \" \", $total_hours";
		
		file_put_contents("./csv/$csv_file", $csv_data);
		send_file_to_browser(str_replace(' ', '_', $account['UserName']."_timesheet_$days_txt.csv"), "./csv/$csv_file");
	} else {
		$error_msg = 'Cannot export timesheet with no entries.';
	}
}

$sites = get_sites();
?><!doctype html>
<html lang="en">
  <head>
    <?php require_once('./inc/admin/blocks/head.inc.php'); ?>
  </head>
  <body>

    <?php require_once('./inc/admin/blocks/menu.inc.php'); ?>
	
	<div class="modal" id="edit_hours_modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Edit Hours</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<p style="text-align:left">Change the number of hours to:</p>
			<input type="text" name="hours_new" id="hours_new" class="form-control" maxlength="5" style="max-width:80px"  data-toggle="update_popover" title="Invalid Value" data-content="This field must be a valid number." required autofocus>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			<button type="button" class="btn btn-primary" id="update_hours_btn" OnClick="update_hours()">Save changes</button>
		  </div>
		</div>
	  </div>
	</div>
	
	<div class="modal" id="edit_day_modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Edit Work Date</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<p style="text-align:left">Change the work day to:</p>
		    <input type="text" name="date_new" id="date_new" class="form-control" maxlength="5" style="display:inline-block;max-width:80px" data-toggle="update_popover" title="Invalid Value" data-content="This field must be a valid number." value="1" required autofocus> <span>days ago</span>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			<button type="button" class="btn btn-primary" id="update_day_btn" OnClick="update_day()">Save changes</button>
		  </div>
		</div>
	  </div>
	</div>
	
	<div class="modal" id="edit_site_modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Edit Work Site</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<p style="text-align:left">Change the site to:</p>
            <select class="custom-select d-block w-100" name="site_new" id="site_new" required>
            <option value="" selected>Choose...</option>
		    <?php
			foreach ($sites as $key => $value) {
				$site_id = $value['SiteID'];
				$site_name = safe_str($value['SiteName']);
				if ($site_name === 'Other') continue;
				echo "<option value='$site_id'>$site_name</option>";
			}
			?>
            </select>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			<button type="button" class="btn btn-primary" id="update_site_btn" OnClick="update_site()">Save changes</button>
		  </div>
		</div>
	  </div>
	</div>

	<div class="main-div">
	
	  <div id="error_box" class="alert alert-warning <?php if (empty($error_msg)) { echo 'd-none'; } ?>" role="alert">
	    <span id='error_msg'><?php if (!empty($error_msg)) safe_echo($error_msg); ?></span>
	  </div>
	  
	  <?php
	  $account = mysqli_fetch_assoc(get_account_byid($_GET['user']));
	  $days = empty($_GET['days']) ? 7 : $_GET['days'];
	  $timesheet = get_hours($_GET['user'], $days);
	  $sorted_ts = format_sheet($timesheet);

	  if ($days == 7) {
	  ?>
	  
	  <h1>Timesheet for last 7 days</h1>
	  <div style="float:right">
	    <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=7&amp;export">Export</a> | <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=30">See last 30 days</a> | <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=all">See all submissions</a>
	  </div>
	  
	  <?php } elseif ($days == 'all') { ?>
	  
	  <h1>Timesheet History</h1>
	  <div style="float:right">
	    <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=all&amp;export">Export</a> | <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=7">See last 7 days</a> | <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=30">See last 30 days</a>
	  </div>
	  
	  <?php } else { ?>
	  
	  <h1>Timesheet for last <?php echo $days; ?> days</h1>
	  <div style="float:right">
	    <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=<?php echo $days; ?>&amp;export">Export</a> | <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=7">See last 7 days</a> | <a href="./admin.php?page=timesheet&amp;user=<?php echo $_GET['user']; ?>&amp;days=all">See all submissions</a>
	  </div>
	  
	  <?php } ?>
	  
	  <h4 class="d-flex justify-content-between align-items-center mb-3">
		<span class="text-muted"><?php safe_echo($account['RealName']); ?></span>
	  </h4>
	  <ul class="list-group mb-3"><?php
		if (empty($sorted_ts)) {
			if ($days != 'all') {
				echo "<p>No submissions made in last $days days.</p>";
			} else {
				echo "<p>No submissions made by this user yet.</p>";
			}
		} else {
			$total_hours = 0;
			$sheet_key = 0;
			foreach ($sorted_ts as $sts_key => $sts_val) {
				$hours = $sts_val['Hours'];
				$total_hours += $hours;
				$mod_txt = empty($sts_val['Modified']) ? '' : '<small>modified on '.$sts_val['Modified'].'</small>';
				$day = empty($sts_val['WorkDate']) ? $sts_val['Created'] : $sts_val['WorkDate'];
				$site = 'Unknown Site';
				if (empty($sts_val['OtherSite'])) {
					$site = get_site($sts_val['SiteID']);
					if (empty($site) || $site === 'N/A') {
						$site = 'Unknown Site';
					} else {
						$site = mysqli_fetch_assoc($site);
						$site = $site['SiteName'];
					}
				} else {
					$site = $sts_val['OtherSite'];
				}
		?>
		<li class="list-group-item d-flex justify-content-between lh-condensed">
		  <div>
			<h6 class="my-0"><?php echo safe_str(date('l', strtotime($day)))." <small>($day)</small>"; ?></h6>
			<small class="text-muted"><?php safe_echo($site); ?></small>
		  </div>
		  <div style="float:right">
		    <span style="float:right;" class="text-muted"><?php echo $mod_txt; ?></span>
			<?php if (!empty($sts_val['Modified'])) { echo '<br />'; } ?>
			<div style="float:right;text-align:center">
		      <span class="text-muted"><span id="hours<?php echo $sheet_key; ?>"><?php echo $hours; ?></span> hours<br /><small><a href='#' OnClick='show_hours_modal(<?php echo $sts_val['SheetID']; ?>)'>edit hours</a> | <a href='#' OnClick='show_day_modal(<?php echo $sts_val['SheetID']; ?>)'>edit day</a> | <a href='#' OnClick='show_site_modal(<?php echo $sts_val['SheetID']; ?>)'>edit site</a> | <a href='#' OnClick="remove_entry('./admin.php?page=timesheet&user=<?php safe_echo($_GET['user']); ?>&remove=<?php echo $sts_val['SheetID']; ?>')">remove</a></small></span>
			<div>
		  </div>
		</li>
		<?php $sheet_key++; } if ($days != 'all') { ?>
		<li class="list-group-item d-flex justify-content-between">
		  <span><?php echo $days; ?> Day Total:</span>
		  <strong><span id="hours_total"><?php echo $total_hours; ?></span> hours</strong>
		</li>
		<?php } ?>
	  </ul>
		<?php } ?>
		
		<br clear="both" />
	    <h5>Create New Entry</h5>
	    <form class="card p-2" id="time_form" method="post" action="">
          <label for="date_val">Day:</label>
		  <select class="custom-select d-block w-100" name="date_val" id="date_val">
			<?php
			for ($i=0; $i<11; $i++) {
				if ($i == 0) {
					echo "<option value='$i' selected>Today (".safe_str(date('l')).")</option>";
				} elseif ($i > 1) {
					echo "<option value='$i'>$i days ago</option>";
				} else {
					echo "<option value='$i'>$i day ago</option>";
				}
			}
			?>
          </select>
          <label for="site_val" style="margin-top:10px">Site:</label>
          <select class="custom-select d-block w-100" name="site_val" id="site_val" data-toggle="site_popover" title="Invalid Value" data-content="Please selection an option from this list." required>
            <option value="" selected>Choose...</option>
		    <?php
			$other_key = 0;
			foreach ($sites as $key => $value) {
				$site_id = $value['SiteID'];
				$site_name = safe_str($value['SiteName']);
				if ($site_name === 'Other') {
					$other_key = $site_id;
					continue;
				}
				echo "<option value='$site_id'>$site_name</option>";
			}
            echo "<option value='$other_key'>Other</option>";
			?>
          </select>
          <label for="other_site" class="d-none" id="osite_label" style="margin-top:10px">Other Site/Project:</label>
		  <input type="text" name="other_site" id="other_site" class="form-control w-100 d-none" data-toggle="other_popover" title="Invalid Value" data-content="This field cannot be empty.">
          <label for="hours_val" style="margin-top:10px">Hours:</label>
		  <input type="text" name="hours_val" id="hours_val" class="form-control w-100" data-toggle="hours_popover" title="Invalid Value" data-content="This field must be a valid number." required>
		  <hr class="mb-3" />
		  <button type="button" class="btn btn-primary" id="submit_btn">Submit</button>
	    </form>

	</div>

	<script>
	var AccountID = <?php safe_echo($_GET['user']); ?>;
	var SheetID = 0;
	var OtherKey = '<?php echo $other_key; ?>';

	function handle_update(response) {
		if (response == 'SUCCESS') {
			$('#error_box').show().removeClass('d-none').removeClass('alert-warning').addClass('alert-success');
			$('#error_msg').html('Timesheet was successfully updated.');
			window.location.href = './admin.php?page=timesheet&user=<?php echo $_GET['user']; ?>';
		} else {
			$('#error_box').show().removeClass('d-none').removeClass('alert-success').addClass('alert-warning');
			$('#error_msg').html(response);
		}
		$('#edit_hours_modal').modal('hide');
		$('#edit_day_modal').modal('hide');
		$('#edit_site_modal').modal('hide');
		$('#update_hours_btn').html('Save Changes');
		$('#update_day_btn').html('Save Changes');
		$('#update_site_btn').html('Save Changes');
		$('#update_hours_btn').removeClass('disabled');
		$('#update_day_btn').removeClass('disabled');
		$('#update_site_btn').removeClass('disabled');
	}

	function handle_error(response) {
		$('#error_box').show().removeClass('d-none').removeClass('alert-success').addClass('alert-warning');
		$('#error_msg').html(response);
		$('#edit_hours_modal').modal('hide');
		$('#edit_day_modal').modal('hide');
		$('#edit_site_modal').modal('hide');
		$('#update_hours_btn').html('Save Changes');
		$('#update_day_btn').html('Save Changes');
		$('#update_site_btn').html('Save Changes');
		$('#update_hours_btn').removeClass('disabled');
		$('#update_day_btn').removeClass('disabled');
		$('#update_site_btn').removeClass('disabled');
	}
	
	function remove_entry(remove_url) {
		if (confirm("Really remove this entry?")) {
			redirect(remove_url);
		}
	}
	
	function update_hours() {
		$('#error_box').hide().addClass('d-none');
		var newHours = $('#hours_new').val();
		if (newHours != '' && !isNaN(newHours)) {
			ajax_post('./inc/jobs/submit.inc.php', 
				'sheet_id='+SheetID+'&new_hours='+newHours, 
				handle_update, handle_error);
			$('#update_hours_btn').addClass('disabled');
			$('#update_hours_btn').html('Updating ...');
		} else {
			$('#hours_new').popover('show');
		}
	}
	
	function update_day() {
		$('#error_box').hide().addClass('d-none');
		var daysAgo = $('#date_new').val();
		if (daysAgo != '' && !isNaN(daysAgo)) {
    		ajax_post('./inc/jobs/submit.inc.php', 
    			'sheet_id='+SheetID+'&days_ago='+daysAgo, 
    			handle_update, handle_error);
    		$('#update_day_btn').addClass('disabled');
    		$('#update_day_btn').html('Updating ...');
		} else {
			$('#date_new').popover('show');
		}
	}
	
	function update_site() {
		$('#error_box').hide().addClass('d-none');
		
		var newSite = $('#site_new').val();
		ajax_post('./inc/jobs/submit.inc.php', 
			'sheet_id='+SheetID+'&new_site='+newSite, 
			handle_update, handle_error);
		$('#update_site_btn').addClass('disabled');
		$('#update_site_btn').html('Updating ...');
	}
	
	function show_hours_modal(sheet_id) {
		SheetID = sheet_id;
		$('#edit_hours_modal').modal('show');
	}
	
	function show_day_modal(sheet_id) {
		SheetID = sheet_id;
		$('#edit_day_modal').modal('show');
	}
	
	function show_site_modal(sheet_id) {
		SheetID = sheet_id;
		$('#edit_site_modal').modal('show');
	}
	
	function site_valid() {
		if ($("#site_val").val() != '' && $("#site_val").val() != OtherKey) {
			return true;
		} else if ($("#site_val").val() == OtherKey && $("#other_site").val() != '') {
			return true;
		} else {
			return false;
		}
	}

	$(document).ready(function() {
		$('[data-toggle="hours_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="site_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="other_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="update_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$("#hours_val").on("change paste keyup", function() {
			var hoursVal = $('#hours_val').val();
			hoursVal = (hoursVal==='') ? 0 : parseFloat(hoursVal);
			if (!isNaN(hoursVal)) {
				$(this).popover('hide');
			}
		});
		$("#hours_new").on("change paste keyup", function() {
			var newHours = $('#hours_new').val();
			newHours = (newHours==='') ? 0 : parseFloat(newHours);
			if (!isNaN(newHours)) {
				$(this).popover('hide');
			}
		});
		$("#date_new").on("change paste keyup", function() {
			var newDate = $('#date_new').val();
			newDate = (newDate==='') ? 0 : parseInt(newDate);
			if (!isNaN(newDate)) {
				$(this).popover('hide');
			}
		});
		$("#site_val").on("change", function() {
			if ($("#site_val").val() == OtherKey) {
				$("#other_site").removeClass('d-none');
				$("#osite_label").removeClass('d-none');
			} else {
				$("#other_site").addClass('d-none');
				$("#osite_label").addClass('d-none');
				$("#other_site").val('');
			}
			$(this).popover('hide');
			$("#other_site").popover('hide');
		});
		$("#other_site").on("change paste keyup", function() {
			if ($("#other_site").val() != '') {
				$(this).popover('hide');
			}
		});
		$("#submit_btn").click(function(){
			if (site_valid()) {
				var hoursVal = $('#hours_val').val();
				hoursVal = (hoursVal==='') ? 0 : parseFloat(hoursVal);
				if (!isNaN(hoursVal)) {
					$(this).addClass('disabled');
					$('#time_form').submit();
				} else {
					$('#hours_val').popover('show');
				}
			} else {
				if ($("#site_val").val() == OtherKey) {
					$('#other_site').popover('show');
				} else {
					$('#site_val').popover('show');
				}
			}
		});
		$('#edit_hours_modal').on('shown.bs.modal', function () {
			$('#hours_new').trigger('focus')
		});
		$('#edit_day_modal').on('shown.bs.modal', function () {
			$('#date_new').trigger('focus')
		});
		$('#edit_site_modal').on('shown.bs.modal', function () {
			$('#site_new').trigger('focus')
		});
	});
	</script>
  </body>
</html>