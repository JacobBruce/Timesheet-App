<?php
function format_time($time_str) {
  return date('Y-m-d H:i:s T', strtotime($time_str.' UTC'));
}

function session_expired($sess_time) {
  if (empty($_SESSION['timeout'])) {
    $_SESSION['timeout'] = time();
    return false;
  } else {
    $time_diff = get_time_difference($_SESSION['timeout'], time());
    if (($time_diff == false) || ($time_diff['hours'] >= $sess_time)) {
      return true; 
    } else {
      return false;
    }
  }
}

function login_state() {
  if (isset($_SESSION['user_data'])) {
    return 'valid';
  } else {
    return 'login';
  }
}

function admin_valid($die=true, $inc=true) {
  if (login_state() === 'valid') {
    if (!$inc || isset($GLOBALS['admin_call'])) {
	  if ($_SESSION['user_data']['IsAdmin']) {
		return true;
	  }
    }
  }
  if ($die) {
    die('ERROR: invalid page access');
  } else {
    return false;
  }
}

function get_img_ext($pic_url) {
  if (file_exists($pic_url.'.jpg')) {
    return '.jpg';
  } elseif (file_exists($pic_url.'.bmp')) {
    return '.bmp';
  } elseif (file_exists($pic_url.'.png')) {
    return '.png';
  } elseif (file_exists($pic_url.'.gif')) {
    return '.gif';
  } else {
    return '';
  }
}
?>
