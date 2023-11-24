<?php
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../session.inc.php');

$hide_crash = true;
$conn = connect_to_db();

if (login_state() === 'valid') {

	if (empty($_POST['sheet_id'])) {
		echo 'ERROR: invalid sheet id provided';
	} else {
		$sheet = mysqli_fetch_assoc(get_timesheet($_POST['sheet_id']));
		if (isset($_POST['new_hours'])) {
			if (admin_valid(false, false) || $sheet['AccountID'] == $_SESSION['user_data']['AccountID']) {
				if (update_timesheet($_POST['sheet_id'], $_POST['new_hours'])) {
					echo 'SUCCESS';
				} else {
					echo 'ERROR: failed to update database';
				}
			} else {
				echo 'ERROR: insufficient permissions';
			}
		} elseif (isset($_POST['days_ago'])) {
			if (admin_valid(false, false)) {
				if (update_tsday($_POST['sheet_id'], $_POST['days_ago'])) {
					echo 'SUCCESS';
				} else {
					echo 'ERROR: failed to update database';
				}
			} else {
				echo 'ERROR: insufficient permissions';
			}
		} elseif (isset($_POST['new_site'])) {
			if (admin_valid(false, false)) {
				if (update_tssite($_POST['sheet_id'], $_POST['new_site'])) {
					echo 'SUCCESS';
				} else {
					echo 'ERROR: failed to update database';
				}
			} else {
				echo 'ERROR: insufficient permissions';
			}
		} else {
			echo 'ERROR: invalid parameters provided';
		}
	}

} else {
	echo 'ERROR: invalid login state';
}
?>