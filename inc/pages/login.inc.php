<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Timesheet App</title>

    <link rel="stylesheet" href="./css/bootstrap.min.css">

    <script src="./js/jquery-3.4.1.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
	<script src="./js/general.lib.js"></script>
	<script src="./js/ajax.lib.js"></script>
	<script src="./js/CryptoJS/sha256.js"></script>
	
    <style>
      .logo {
	    margin-bottom:20px;
	  }
    </style>

    <link href="./css/signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
    <form name="login_form" id="login_form" class="form-signin" method="post" action="">
	  <div id="error_box" class="alert alert-success" role="alert">
		<span id='error_msg'></span>
	  </div>
      <img class="img-fluid logo" src="<?php echo $logo_path; ?>">
      <?php
      if ($show_login_msg && !isset($admin_call)) {
          $login_msg = file_get_contents('inc/login_msg.txt');
		  if (!empty($login_msg)) {
            echo '<div class="alert alert-info" role="alert">';
            safe_echo($login_msg);
            echo '</div>';
		  }
      }
      ?>
	  <h1><?php 
	  if (isset($_GET['admin'])) {
	    echo 'Admin Login';
      } else {
	    echo 'User Login';
      }
	  ?></h1>
      <h3 class="h3 mb-3 font-weight-normal"></h3>
      <label for="user_txt" class="sr-only">Username</label>
      <input type="text" name="user_txt" id="user_txt" class="form-control" placeholder="Username" maxlength="50" required autofocus>
      <label for="pass_txt" class="sr-only">Password</label>
      <input type="password" name="pass_txt" id="pass_txt" class="form-control" placeholder="Password" maxlength="99" required>
      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      <p class="mt-5 mb-3 text-muted">&copy; <?php safe_echo($trade_name.' '.date('Y')); ?></p>
    </form>

	<script language="javascript">
	var ip_hash = '<?php safe_echo(get_ip_hash()); ?>';
	var rounds = <?php echo $hash_rounds; ?>;
	var qry_str = '<?php echo url_query_str(); ?>';
	
	$('#error_box').hide();

	function show_tooltip() {
	  $('#conn_label').tooltip('show');
	}

	function hash_pass(pass) {
	  var result = CryptoJS.SHA256(CryptoJS.SHA256(pass)+pass);
	  for (var i=0; i<rounds; i++) {
		result = CryptoJS.SHA256(result.toString());
	  }
	  return result.toString();
	}

	function handle_login(response) {
	  var res_arr = response.split(':');
	  if (res_arr[0] == 'success') {
		$('#error_box').show().removeClass('alert-warning').addClass('alert-success');
		$('#error_msg').html('Credentials Verified<br />Redirecting ...');
		<?php if (isset($_GET['admin'])) { ?>
		setTimeout(function(){redirect('./admin.php'+qry_str);}, 1000);
		<?php } else { ?>
		setTimeout(function(){redirect('./?page=home');}, 1000);
		<?php } ?>
	  } else {
		$('#error_box').show().removeClass('alert-success').addClass('alert-warning');
		$('#error_msg').html(response);
	  }
	}

	function handle_error(response) {
		$('#error_box').show().removeClass('alert-success').addClass('alert-warning');
		$('#error_msg').html(response);
	}

	$("#login_form").submit(function(event) {
	  event.preventDefault();
	  var pass_text = $("#pass_txt").val();
	  var user_text = $("#user_txt").val();
	  var ip_lock = $('input[name=lock]:checked', '#login_form').val();
	  var pass_hash = hash_pass(pass_text);
	  var toke_hash = CryptoJS.SHA256(ip_hash+pass_hash);
	  ajax_post('./inc/jobs/login.inc.php', 
	  'user='+user_text+'&pass='+toke_hash+
	  '&lock='+ip_lock<?php if (isset($_GET['admin'])) 
	  { echo "+'&admin=1'"; } ?>, handle_login, handle_error);
	});
	</script>
  </body>
</html>