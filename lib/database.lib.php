<?php

/*
START CRITICAL/CONNECTION DB FUNCTIONS
*/

$mysql_regx_sow = '';
$mysql_regx_eow = '';

function graceful_crash($sql_str='N/A', $sql_obj=null) {

  if (isset($GLOBALS['conn'])) {
    $_SESSION['sql_errno'] = trim(trim($GLOBALS['conn']->errno, ' '), "\n");
    $_SESSION['sql_error'] = trim(trim($GLOBALS['conn']->error, ' '), "\n");
    $_SESSION['sql_query'] = trim(trim($sql_str, ' '), "\n");
  } else {
    $_SESSION['sql_errno'] = trim(trim($sql_obj->connect_errno, ' '), "\n");
    $_SESSION['sql_error'] = trim(trim($sql_obj->connect_error, ' '), "\n");
    $_SESSION['sql_query'] = $sql_str;
  }
  
  if (!isset($GLOBALS['hide_crash'])) {
    redirect($GLOBALS['base_url'].'error.php');
  }
}

function connect_to_db() {

  if (isset($GLOBALS['conn'])) {
    return $GLOBALS['conn'];

  } elseif (isset($GLOBALS['db_port'])) {
  
	global $db_port;
	global $db_server;
	global $db_database;
	global $db_username;
	global $db_password;
	global $mysql_regx_sow;
	global $mysql_regx_eow;
	
    $mysqli = new mysqli($db_server, $db_username, 
	          $db_password, $db_database, $db_port);
			  
    if ($mysqli->connect_errno) {
      graceful_crash('N/A', $mysqli);
    } else {
      if ($mysqli->server_version < 80004) {
        $mysql_regx_sow = '[[:<:]]';
		$mysql_regx_eow = '[[:>:]]';
      } else {
        $mysql_regx_sow = '\\b';
		$mysql_regx_eow = '\\b';
      }
  
	  return $mysqli;
	}
  } else {
    die('ERROR: no access to db config');
  }
}

function mysqli_result($res, $row, $field=0) { 
  $res->data_seek($row); 
  $datarow = $res->fetch_array(); 
  return $datarow[$field]; 
}

function mysqli_now() {
  global $conn;
  $sql_str = "SELECT UTC_TIMESTAMP()";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    if ($sql_result->num_rows <= 0) {
      return 'N/A';
    } else {
      return mysqli_result($sql_result, 0);
    }
  } else {
	graceful_crash($sql_str);
  }
}

/*
START GENERAL/GLOBAL DB FUNCTIONS
*/

function select_basic($sel_str) {
  global $conn;
  $sql_str = "SELECT $sel_str";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    if ($sql_result->num_rows <= 0) {
      return 'N/A';
    } else {
      return $sql_result;
    }
  } else {
	graceful_crash($sql_str);
  }
}

function select_from($table, $sel_str, $rules='') {
  global $conn;
  $sql_str = "
  SELECT $sel_str
  FROM $table
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    if ($sql_result->num_rows <= 0) {
      return 'N/A';
    } else {
      return $sql_result;
    }
  } else {
	graceful_crash($sql_str);
  }
}

function select_from_where($table, $sel_str, $where, $rules='') {
  global $conn;
  $sql_str = "
  SELECT $sel_str
  FROM $table
  WHERE $where
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    if ($sql_result->num_rows <= 0) {
      return 'N/A';
    } else {
      return $sql_result;
    }
  } else {
	graceful_crash($sql_str);
  }
}

function insert_into($table, $ins_str, $values) {
  global $conn;
  $sql_str = "
  INSERT INTO $table (".$ins_str.")
  VALUES (".$values.")";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    return $conn->insert_id;
  } else {
	graceful_crash($sql_str);
  }
}

function insert_into_where($table, $ins_str, $values, $where) {
  global $conn;
  $sql_str = "
  INSERT INTO $table (".$ins_str.")
  VALUES (".$values.")
  WHERE $where";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {
    return $conn->insert_id;
  } else {
	graceful_crash($sql_str);
  }
}

function update_where($table, $set_str, $value, $where, $rules='') {
  global $conn;
  $sql_str = "
  UPDATE $table 
  SET $set_str = $value 
  WHERE $where
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {		
    return $sql_result;
  } else {
	graceful_crash($sql_str);
  }
}

function multi_update($table, $set_str, $where, $rules='') {
  global $conn;
  $sql_str = "
  UPDATE $table 
  SET $set_str 
  WHERE $where
  $rules";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {		
    return $sql_result;
  } else {
	graceful_crash($sql_str);
  }
}

function delete_from($table, $where) {
  global $conn;
  $sql_str = "
  DELETE FROM $table 
  WHERE $where";
  $sql_result = $conn->query($sql_str);
  if ($sql_result) {		
    return $sql_result;
  } else {
	graceful_crash($sql_str);
  }
}

/*
START TIMESHEET DB FUNCTIONS
*/

function submit_hours($acc_id, $days_ago, $hours, $site_id, $other_site='') {
  $acc_id = (int) $acc_id;
  $days_ago = (int) $days_ago;
  $site_id = (int) $site_id;
  $hours = (float) $hours;
  if (empty($other_site)) {
    if ($days_ago == 0) {
      return insert_into('Timesheets', 'AccountID, SiteID, Hours, OtherSite', 
                         "$acc_id, $site_id, $hours, ''");
	} else {
      return insert_into('Timesheets', 'AccountID, SiteID, Hours, OtherSite, WorkDate', 
                         "$acc_id, $site_id, $hours, '', DATE_SUB(NOW(), INTERVAL $days_ago DAY)");
	}
  } else {
    $other_site = safe_sql_str($other_site);
    if ($days_ago == 0) {
      return insert_into('Timesheets', 'AccountID, Hours, OtherSite', 
                         "$acc_id, $hours, '$other_site'");
	} else {
      return insert_into('Timesheets', 'AccountID, Hours, OtherSite, WorkDate', 
                         "$acc_id, $hours, '$other_site', DATE_SUB(NOW(), INTERVAL $days_ago DAY)");
	}
  }
}

function get_hours($acc_id, $days=7) {
  $acc_id = (int) $acc_id;
  if ($days === 'all') {
	  return select_from('Timesheets', '*', "WHERE AccountID = $acc_id");
  } else {
	  $days = (int) $days;
	  return select_from('Timesheets', '*', "WHERE AccountID = $acc_id AND (".
	  "(WorkDate IS NULL AND Created BETWEEN DATE_SUB(NOW(), INTERVAL $days DAY) AND NOW()) OR ".
	  "(WorkDate IS NOT NULL AND WorkDate BETWEEN DATE_SUB(NOW(), INTERVAL $days DAY) AND NOW()))");
  }
}

function get_range_hours($acc_id, $start_date, $end_date) {
	  $start_date = safe_sql_str($start_date);
	  $end_date = safe_sql_str($end_date);
	  return select_from('Timesheets', '*', "WHERE AccountID = $acc_id AND (".
	  "(WorkDate IS NULL AND Created BETWEEN '$start_date' AND DATE_ADD('$end_date', INTERVAL 1 DAY)) OR ".
	  "(WorkDate IS NOT NULL AND WorkDate BETWEEN '$start_date' AND DATE_ADD('$end_date', INTERVAL 1 DAY)))");
}

function get_week($day_sub=2) {
	return mysqli_result(select_basic("WEEKOFYEAR(DATE_SUB(NOW(), INTERVAL $day_sub DAY))"), 0);
}

function get_week_hours($acc_id, $week='this', $day_sub=2) {
  $acc_id = (int) $acc_id;
  $week = safe_sql_str($week);
  if ($week === 'all') {
      return select_from('Timesheets', '*', "WHERE AccountID = $acc_id");
  } elseif ($week === 'this') {
	  return select_from('Timesheets', '*', "WHERE AccountID = $acc_id AND (".
	  "(WorkDate IS NULL AND YEAR(Created) = YEAR(NOW()) AND WEEKOFYEAR(DATE_SUB(Created, INTERVAL $day_sub DAY)) = WEEKOFYEAR(DATE_SUB(NOW(), INTERVAL $day_sub DAY))) OR ".
	  "(WorkDate IS NOT NULL AND YEAR(WorkDate) = YEAR(NOW()) AND WEEKOFYEAR(DATE_SUB(WorkDate, INTERVAL $day_sub DAY)) = WEEKOFYEAR(DATE_SUB(NOW(), INTERVAL $day_sub DAY))))");
  } else {
	  return select_from('Timesheets', '*', "WHERE AccountID = $acc_id AND (".
	  "(WorkDate IS NULL AND YEAR(Created) = YEAR(NOW()) AND WEEKOFYEAR(DATE_SUB(Created, INTERVAL $day_sub DAY)) = (WEEKOFYEAR(DATE_SUB(NOW(), INTERVAL $day_sub DAY))-1)) OR ".
	  "(WorkDate IS NOT NULL AND YEAR(WorkDate) = YEAR(NOW()) AND WEEKOFYEAR(DATE_SUB(WorkDate, INTERVAL $day_sub DAY)) = (WEEKOFYEAR(DATE_SUB(NOW(), INTERVAL $day_sub DAY))-1)))");
  }
}

function get_timesheet($sheet_id) {
  $sheet_id = (int) $sheet_id;
  return select_from('Timesheets', '*', "WHERE SheetID = $sheet_id LIMIT 1");
}

function list_timesheets($start, $count=20) {
  $start = (int) $start;
  return select_from('Timesheets', '*', "ORDER BY SheetID DESC LIMIT $start, $count");
}

function count_timesheets() {
  return mysqli_result(select_from('Timesheets', 'COUNT(*)'), 0);
}

function update_timesheet($sheet_id, $new_hours) {
  $sheet_id = (int) $sheet_id;
  $new_hours = (float) $new_hours;
  return multi_update('Timesheets', "Hours = $new_hours, Modified = NOW()", "SheetID = $sheet_id", "LIMIT 1");
}

function update_tsday($sheet_id, $days_ago) {
  $sheet_id = (int) $sheet_id;
  $days_ago = (float) $days_ago;
  return update_where('Timesheets', "WorkDate", "DATE_SUB(NOW(), INTERVAL $days_ago DAY)", "SheetID = $sheet_id");
}

function update_tssite($sheet_id, $new_site) {
  $sheet_id = (int) $sheet_id;
  $new_site = (int) $new_site;
  return update_where('Timesheets', "SiteID", "$new_site", "SheetID = $sheet_id");
}

function remove_timesheet($sheet_id) {
  $sheet_id = (int) $sheet_id;
  return delete_from('Timesheets', "SheetID = $sheet_id");
}

/*
START PROJECT/SITE DB FUNCTIONS
*/

function create_site($site_name, $enabled=1) {
  $enabled = (int) $enabled;
  $site_name = safe_sql_str($site_name);
  return insert_into('Sites', 'Enabled, SiteName', "$enabled, '$site_name'");
}

function get_site($site_id) {
  $site_id = (int) $site_id;
  return select_from('Sites', '*', "WHERE SiteID = $site_id LIMIT 1");
}

function get_all_sites() {
  return select_from('Sites', '*');
}

function get_sites() {
  return select_from('Sites', '*', "WHERE Enabled = 1");
}

function count_sites() {
  return mysqli_result(select_from('Sites', 'COUNT(*)'), 0);
}

function enable_site($site_id) {
  $site_id = (int) $site_id;
  return update_where('Sites', 'Enabled', '1', "SiteID = $site_id", "LIMIT 1");
}

function disable_site($site_id) {
  $site_id = (int) $site_id;
  return update_where('Sites', 'Enabled', '0', "SiteID = $site_id", "LIMIT 1");
}

function update_site($site_id, $new_name) {
  $site_id = (int) $site_id;
  $new_name = safe_sql_str($new_name);
  return update_where('Sites', "SiteName", "'$new_name'", "SiteID = $site_id");
}

/*
START ACCOUNT DB FUNCTIONS
*/

function create_account($user_name, $real_name, $pass_hash, $employer, $is_admin=0) {
  $is_admin = (int) $is_admin;
  $user_name = safe_sql_str($user_name);
  $real_name = safe_sql_str($real_name);
  $pass_hash = safe_sql_str($pass_hash);
  $employer = safe_sql_str($employer);
  return insert_into('Accounts', 'IsAdmin, Employer, UserName, RealName, PassHash', "$is_admin, '$employer', '$user_name', '$real_name', '$pass_hash'");
}

function update_account($acc_id, $user_name, $real_name, $pass_hash, $employer) {
  $acc_id = (int) $acc_id;
  $user_name = safe_sql_str($user_name);
  $real_name = safe_sql_str($real_name);
  $pass_hash = safe_sql_str($pass_hash);
  $employer = safe_sql_str($employer);
  return multi_update('Accounts', "Employer = '$employer', UserName = '$user_name', RealName = '$real_name', PassHash = '$pass_hash'", "AccountID = $acc_id", "LIMIT 1");
}

function get_account_byid($acc_id) {
  $acc_id = (int) $acc_id;
  return select_from_where('Accounts', '*', "AccountID = $acc_id", "LIMIT 1"); 
}

function get_account_byuser($user) {
  $user = safe_sql_str($user);
  return select_from_where('Accounts', '*', "UserName = '$user'", "LIMIT 1"); 
}

function set_lock_count($acc_id, $fail_count) {
  $acc_id = (int) $acc_id;
  $fail_count = (int) $fail_count;
  return update_where('Accounts', 'FailCount', $fail_count, "AccountID = $acc_id", "LIMIT 1");
}

function set_last_time($acc_id, $utc_now, $last_ip) {
  $acc_id = (int) $acc_id;
  $last_ip = safe_sql_str($last_ip);
  return multi_update('Accounts', "LastIP = '$last_ip', LastTime = '$utc_now'", 
                      "AccountID = $acc_id", "LIMIT 1");
}

function set_account_pass($acc_id, $pass_hash) {
  $acc_id = (int) $acc_id;
  $pass_hash = safe_sql_str($pass_hash);
  return update_where('Accounts', 'PassHash', "'$pass_hash'", "AccountID = $acc_id", "LIMIT 1");
}

function set_account_name($acc_id, $email, $name, $phone) {
  $acc_id = (int) $acc_id;
  $email = safe_sql_str($email);
  $name = safe_sql_str($name);
  $phone = safe_sql_str($phone);
  return update_where('Accounts', 'RealName', "'$name'", "AccountID = $acc_id", "LIMIT 1");
}

function remove_account($acc_id) {
  $acc_id = (int) $acc_id;
  return delete_from('Accounts', "AccountID = $acc_id");
}

function enable_account($acc_id) {
  $acc_id = (int) $acc_id;
  return update_where('Accounts', 'Disabled', '0', "AccountID = $acc_id", "LIMIT 1");
}

function disable_account($acc_id) {
  $acc_id = (int) $acc_id;
  return update_where('Accounts', 'Disabled', '1', "AccountID = $acc_id", "LIMIT 1");
}

function list_all_accounts() {
  return select_from('Accounts', '*', 'ORDER BY RealName ASC');
}

function count_accounts() {
  return mysqli_result(select_from('Accounts', 'COUNT(*)'), 0);
}
?>
