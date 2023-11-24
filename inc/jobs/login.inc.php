<?php
require_once(dirname(__FILE__).'/../../lib/common.lib.php');
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../session.inc.php');

$hide_crash = true;
$conn = connect_to_db();
$utc_now = mysqli_now();

if (($_SERVER['REQUEST_METHOD'] == 'POST') && !empty($_POST['user'])) {
  if (empty($_POST['pass'])) {
    echo 'Password Empty';
  } else {
    if (!validate_maxlength($_POST['user'], 50) || strlen($_POST['pass']) <> 64) {
	  echo 'Invalid Action';
	} else {
	  $account = get_account_byuser($_POST['user']);
	  if (!empty($account) && ($account !== 'N/A')){
	    $account = mysqli_fetch_assoc($account);
	    if ($account['Disabled'] == 1) {
	        die('Account is disabled.');
	    }
		$time_diff = get_time_difference($account['LastTime'], $utc_now);
		$time_left = $login_lock_time - $time_diff['minutes'];
		if ($account['FailCount'] >= $login_fail_limit) {
		  if ($time_left <= 0) {
		    $account['FailCount'] = 0;
		    if (!set_lock_count($account['AccountID'], $account['FailCount'])) {
			  die('Database Error');
			}
		  }
		}
		if (!$account['IsAdmin'] || $account['FailCount'] < $login_fail_limit) {
		  $toke_hash = hash('sha256', get_ip_hash().$account['PassHash']);
		  if ($_POST['pass'] == $toke_hash) {
		    set_last_time($account['AccountID'], $utc_now, get_remote_ip());
			session_regenerate_id();
	        $_SESSION['ip_address'] = get_remote_ip();
			$_SESSION['user_data'] = $account;
			$_SESSION['csrf_token'] = rand_str();
			echo "success";
		  } else {
			$account['FailCount']++;
			if (!set_lock_count($account['AccountID'], $account['FailCount'])) {
			  die('Database Error');
			}
			echo 'Incorrect password. ';
			if ($account['FailCount'] == $login_fail_limit) {
			  if (!set_last_time($account['AccountID'], $utc_now, get_remote_ip())) {
			    die('<br />Database Error');
			  }
			  if ($account['IsAdmin']) echo "Account locked for $login_lock_time minutes.";
			}
		  }
		} else {
		  echo "Account locked for another $time_left minutes.";
		}
	  } else {
		echo 'No such account.';
	  }
	}
  }
}
?>