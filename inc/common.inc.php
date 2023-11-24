<?php
// call required includes
require_once('lib/common.lib.php');
require_once('inc/config.inc.php');
require_once('inc/session.inc.php');

// set timezone (can be from session)
date_default_timezone_set($time_zone);

// connect to database
$conn = connect_to_db();

// get current page
if (empty($_GET['page'])) {
  $page = 'home';
} else {
  $page = urlencode($_GET['page']);
}

// show the login page if necessary
if (login_state() !== 'valid') {
  $page = 'login';
}

// clean any form input
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $_POST = clean_form_input($_POST);
  // check if in admin area or account area
  /*if (isset($admin_call) || $page === 'user') {
    // make sure CSRF token was passed to us
    if (empty($_SESSION['csrf_token']) || 
	$_SESSION['csrf_token'] !== $_POST['csrf_token']) {
	  die(LANG('INVALID_ACCESS'));
	}
  }*/
}

// set product page title dynamically
$page_titles['item'] = (empty($file['FileName'])) ? 'Page Not Found' : $file['FileName'];

// get page title
if (isset($admin_call)) {
  $page_title = $page_titles['admin'];
} elseif (array_key_exists($page, $page_titles)) {
  $page_title = $page_titles[$page];
} else {
  if (file_exists('inc/pages/'.$page.'.inc.php')) {
    $page_title = $page_titles['untitled'];
  } else {
    $page_title = $page_titles['notfound'];
  }
}
?>