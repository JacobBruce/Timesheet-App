<?php
// turn on/off error reporting (0 when live)
$error_level = E_ALL;

// show sql debug info on error (false when live)
$debug_sql = true;

// database connection settings
$db_port = 3306;
$db_server = 'localhost';
$db_database = 'timesheet';
$db_username = 'root';
$db_password = 'toor';

// install directory ('/' if installed at root)
$install_dir = '/timesheet/';

// disable/enable website
$site_enabled = true;

// display message when disabled
$disable_msg = 'Website is currently undergoing maintenance.';

// website name
$site_name = 'Timesheet App';

// business logo
$logo_path = './img/logo.png';

// business name
$trade_name = 'My Business';

// hide/show login message
$show_login_msg = false;

// max session time (hours)
$sess_time = 4;

// number of rounds used to hash passwords
$hash_rounds = 8;

// maximum number of failed login attempts
$login_fail_limit = 5;

// minutes to lock account if over fail limit
$login_lock_time = 60;

// app language (front end only)
$locale = 'en-US';

// default time zone used by server
$time_zone = 'UTC';

/* IGNORE ANYTHING UNDER THIS LINE */
$inter_prot = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
$serv_name = empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['SERVER_NAME'];
$serv_name = ($_SERVER['SERVER_PORT'] == 80) ? $serv_name : $serv_name.':'.$_SERVER['SERVER_PORT'];
$http_host = empty($_SERVER['HTTP_HOST']) ? $serv_name : $_SERVER['HTTP_HOST'];
$base_url = $inter_prot.$http_host.$install_dir;
bcscale(8);
ini_set('display_errors', 1);
error_reporting($error_level);
?>