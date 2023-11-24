<?php admin_valid();

$week = get_week();
  
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

if (isset($_GET['week'])) {
	$week_int = ($_GET['week'] == 'this') ? $week : $week-1;
	$accounts = list_all_accounts();
	$csv_file = 'week_'.$week_int.'_timesheet.csv';
	$csv_data = array();
	
	if ($_GET['week'] == 'all') {
		$csv_file = 'all_timesheets.csv';
	} else if ($_GET['week'] == 'range') {
		$csv_file = 'custom_timesheets.csv';
	}
	
	$csv_data['0'] = "\"COMPANY\", \"EMPLOYEE\", \"SITE\", \"DATE\", \"HOURS\", \"TOTAL HOURS\"\n\n";
	
	foreach ($employers as $emkey => $emval) {
		$csv_data[$emval] = '';
	}

	while ($row = mysqli_fetch_assoc($accounts))
	{
		if ($row['IsAdmin'] == 1) continue;

		if ($_GET['week'] == 'range') {
			$timesheet = get_range_hours($row['AccountID'], $_GET['start'], $_GET['end']);
		} else {
			$timesheet = get_week_hours($row['AccountID'], $_GET['week']);
		}
		$sorted_ts = format_sheet($timesheet);
		
		if (!empty($sorted_ts)) {
			$total_hours = 0;
			$employer = str_replace(',', '-', empty($row['Employer']) ? 'n/a' : $row['Employer']);
			$real_name = str_replace('"', "'", str_replace(',', '.', $row['RealName'].' ('.$row['UserName'].')'));
			$emp_str = $employer;
			
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
				$day = date('l', strtotime($day)).' ('.str_replace(',', '-', $day).')';
				$site = str_replace(',', '-', $site);
				$hours = str_replace(',', '.', $hours);
				$csv_data[$employer] .= "\"$emp_str\", \"$real_name\", \"$site\", \"$day\", $hours, \" \"\n";
				$real_name = ' ';
				$emp_str = ' ';
			}
			
			$csv_data[$employer] = substr($csv_data[$employer], 0, -4)."$total_hours\n\n";
		}
	}

	$csv_final = '';
	foreach ($csv_data as $key => $val) {
		$csv_final .= $val;
	}
	file_put_contents("./csv/$csv_file", $csv_final);
	send_file_to_browser($csv_file, "./csv/$csv_file");
}
?><!doctype html>
<html lang="en">
  <head>
    <?php require_once('./inc/admin/blocks/head.inc.php'); ?>
  </head>
  <body>

    <?php require_once('./inc/admin/blocks/menu.inc.php'); ?>

	<div class="main-div">
	
	  <h1>Export Timesheets</h1>
	  <hr />

	  <p><a href="./admin.php?page=export&amp;week=this">Export this week (<?php echo $week; ?>)</a></p>
	  <p><a href="./admin.php?page=export&amp;week=last">Export last week (<?php echo $week-1; ?>)</a></p>
	  <p><a href="#" data-toggle="modal" data-target="#range_modal">Export custom range</a></p>
	  <p><a href="./admin.php?page=export&amp;week=all">Export all</a></p>
	  
		<div class="modal" id="range_modal" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title">Select Custom Range</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body">
				<label for="start_date">Start&nbsp;Date:</label>
				<input type="date" id="starti" name="start_date" /><br />
				<label for="end_date">End&nbsp;Date:&nbsp;</label>
				<input type="date" id="endi" name="end_date" />
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="submit_modal()">Submit</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			  </div>
			</div>
		  </div>
		</div>

	</div>
	
	<script>
	function submit_modal() {
		var startArg = encodeURIComponent($("#starti").val());
		var endArg = encodeURIComponent($("#endi").val());
		if (startArg != '' && endArg != '') {
			redirect('./admin.php?page=export&week=range&start='+startArg+'&end='+endArg);
		} else {
			alert('You must choose start and end dates!');
		}
	}
	</script>

  </body>
</html>