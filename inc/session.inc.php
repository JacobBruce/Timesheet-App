<?php
// make sure we have access to config vars
require_once(dirname(__FILE__).'/config.inc.php');

// start the session
session_start();

// check session status
if (session_expired($sess_time)) {
  session_unset();
}

// save account id to global variable
if (isset($_SESSION['user_data']['AccountID'])) {
  $account_id = $_SESSION['user_data']['AccountID'];
}

// save IP address to session
if (!isset($_SESSION['ip_address'])) {
  $_SESSION['ip_address'] = get_remote_ip();
}
?>