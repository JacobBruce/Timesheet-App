<?php
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../session.inc.php');

$hide_crash = true;
$conn = connect_to_db();

if (login_state() === 'valid') {

	if (empty($_POST['site_id']) || empty($_POST['new_name'])) {
		echo 'ERROR: invalid parameters provided';
	} else {
		$sheet = mysqli_fetch_assoc(get_site($_POST['site_id']));
		if (admin_valid(false, false)) {
			if (update_site($_POST['site_id'], $_POST['new_name'])) {
				echo 'SUCCESS';
			} else {
				echo 'ERROR: failed to update database';
			}
		} else {
			echo 'ERROR: insufficient permissions';
		}
	}

} else {
	echo 'ERROR: invalid login state';
}
?>