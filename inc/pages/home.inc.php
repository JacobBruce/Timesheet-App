<?php
if (isset($_POST['hours_val'])) {
	$work_date = 0;
	if (isset($_POST['date_val'])) $work_date = $_POST['date_val'];
	if (empty($_POST['other_site'])) {
		$row_id = submit_hours($_SESSION['user_data']['AccountID'], $work_date, $_POST['hours_val'], $_POST['site_val']);
	} else {
		$row_id = submit_hours($_SESSION['user_data']['AccountID'], $work_date, $_POST['hours_val'], '', $_POST['other_site']);
	}
	if (empty($row_id)) {
		$error_msg = 'An unexpected error occurred, please try again later.';
	}
}

$sites = get_sites();
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Timesheet App</title>

    <link rel="stylesheet" href="./css/bootstrap.min.css">

    <script src="./js/jquery-3.4.1.min.js"></script>
    <script src="./js/bootstrap.bundle.min.js"></script>
	<script src="./js/general.lib.js"></script>
	<script src="./js/ajax.lib.js"></script>
	<script src="./js/CryptoJS/sha256.js"></script>

    <link href="./css/user.css" rel="stylesheet">
  </head>
  <body class="text-center">

	<div style="position:absolute;top:10px;right:20px;">
	  <a href="./?page=logout" class="text-right" />LOGOUT</a>
	</div>
	
	<div class="modal" id="edit_modal" tabindex="-1" role="dialog">
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
			<button type="button" class="btn btn-primary" id="update_btn" OnClick="update_sheet()">Save changes</button>
		  </div>
		</div>
	  </div>
	</div>
	
    <div class="main-div">

	  <div id="error_box" class="alert alert-warning <?php if (empty($error_msg)) { echo 'd-none'; } ?>" role="alert">
	    <span id='error_msg'><?php if (!empty($error_msg)) safe_echo($error_msg); ?></span>
	  </div>

	  <h4 class="d-flex justify-content-between align-items-center mb-3">
		<span class="text-muted"><?php safe_echo($_SESSION['user_data']['RealName']); ?></span>
	  </h4>
	  <ul class="list-group mb-3"><?php
	  
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

		$timesheet = get_week_hours($_SESSION['user_data']['AccountID']);
		$sorted_ts = format_sheet($timesheet);
	  
		if (empty($sorted_ts)) {
			echo '<p>No submissions made this week.</p>';
		} else {
			$total_hours = 0;
			$sheet_key = 0;
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
		?>
		<li class="list-group-item d-flex justify-content-between lh-condensed">
		  <div>
			<h6 class="my-0"><?php safe_echo(date('l', strtotime($day))); ?></h6>
			<small class="text-muted"><?php safe_echo($site); ?></small>
		  </div>
		  <span class="text-muted"><span id="hours<?php echo $sheet_key; ?>"><?php echo $hours; ?></span> hours<br />(<a href='#' OnClick='show_modal(<?php echo $sts_val['SheetID'].", $sheet_key"; ?>)'>edit</a>)</span>
		</li>
		<?php $sheet_key++; } ?>
		<li class="list-group-item d-flex justify-content-between">
		  <span>Current Week Total:</span>
		  <strong><span id="hours_total"><?php echo $total_hours; ?></span> hours</strong>
		</li>
		<?php } ?>
	  </ul>

	  <h5>Create New Entry</h5>
	  <form class="card p-2" id="time_form" method="post" action="">
          <label for="date_val">Day:</label>
		  <select class="custom-select d-block w-100" name="date_val" id="date_val">
			<?php
			for ($i=0; $i<2; $i++) {
				if ($i == 0) {
					echo "<option value='$i' selected>Today (".safe_str(date('l')).")</option>";
				} else {
					echo "<option value='$i'>Yesterday (".safe_str(date('l', time()-86400)).")</option>";
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
            //echo "<option value='$other_key'>Other</option>";
			?>
          </select>
          <label for="other_site" class="d-none" id="osite_label" style="margin-top:10px">Other Site/Project:</label>
		  <input type="text" name="other_site" id="other_site" class="form-control w-100 d-none" data-toggle="other_popover" title="Invalid Value" data-content="This field cannot be empty.">
          <label for="hours_val" style="margin-top:10px">Hours:</label>
		  <input type="text" name="hours_val" id="hours_val" class="form-control w-100" data-toggle="hours_popover" title="Invalid Value" data-content="This field must be a valid number." required>
		  <hr class="mb-3" />
		  <button type="button" class="btn btn-primary" id="submit_btn">Submit</button>
	  </form>

      <p class="mt-5 mb-3 text-muted">&copy; <?php safe_echo($trade_name.' '.date('Y')); ?></p>
    </div>
	
	<script>
	var SheetID = 0;
	var SheetKey = 0;
	var OtherKey = '<?php echo $other_key; ?>';

	function handle_update(response) {
		if (response == 'SUCCESS') {
			$('#error_box').show().removeClass('d-none').removeClass('alert-warning').addClass('alert-success');
			$('#error_msg').html('Timesheet was successfully updated.');
			var oldHours = $('#hours'+SheetKey).html();
			var newHours = parseFloat($('#hours_new').val());
			var hoursDiff = newHours - oldHours;
			var hoursSum = parseFloat($('#hours_total').html()) + hoursDiff;
			$('#hours'+SheetKey).html(newHours);
			$('#hours_total').html(hoursSum);
		} else {
			$('#error_box').show().removeClass('d-none').removeClass('alert-success').addClass('alert-warning');
			$('#error_msg').html(response);
		}
		$('#edit_modal').modal('hide');
		$('#update_btn').html('Save Changes');
		$('#update_btn').removeClass('disabled');
	}

	function handle_error(response) {
		$('#error_box').show().removeClass('d-none').removeClass('alert-success').addClass('alert-warning');
		$('#error_msg').html(response);
		$('#edit_modal').modal('hide');
		$('#update_btn').html('Save Changes');
		$('#update_btn').removeClass('disabled');
	}
	
	function update_sheet() {
		$('#error_box').hide().addClass('d-none');
		var newHours = $('#hours_new').val();
		if (newHours != '' && !isNaN(newHours)) {
			ajax_post('./inc/jobs/submit.inc.php', 
			'sheet_id='+SheetID+'&new_hours='+newHours, 
			handle_update, handle_error);
			$('#update_btn').addClass('disabled');
			$('#update_btn').html('Updating ...');
		} else {
			$('#hours_new').popover('show');
		}
	}
	
	function show_modal(sheet_id, sheet_key) {
		SheetID = sheet_id;
		SheetKey = sheet_key;
		$('#edit_modal').modal('show');
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
			var newHours = parseFloat($('#hours_new').val());
			if (!isNaN(newHours)) {
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
			$(".popover").on('click', function () {
				$(this).popover('hide');
			});
		});
		$('#edit_modal').on('shown.bs.modal', function () {
			$('#hours_new').trigger('focus')
		});
	});
	</script>
  </body>
</html>