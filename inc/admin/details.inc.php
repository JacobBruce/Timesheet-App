<?php admin_valid(); 
$error_msg = '';

if (!empty($_POST['employer']) || !empty($_POST['new_user_name']) || !empty($_POST['new_real_name']) || !empty($_POST['new_pass_txt'])) {
	$account = mysqli_fetch_assoc(get_account_byid($_GET['user']));
	$new_uname=''; $new_rname=''; $new_phash=''; $employer='';
	if (empty($_POST['new_pass_txt'])) {
		$new_phash = $account['PassHash'];
	} else {
		$error_msg .= 'Password successfully updated.<br/>';
		$new_phash = pass_hash($_POST['new_pass_txt'], $hash_rounds);
	}
	if (empty($_POST['new_user_name'])) {
		$new_uname = $account['UserName'];
	} else {
		$error_msg .= 'Username successfully updated.<br/>';
		$new_uname = $_POST['new_user_name'];
	}
	if (empty($_POST['new_real_name'])) {
		$new_rname = $account['RealName'];
	} else {
		$error_msg .= 'Real name successfully updated.<br/>';
		$new_rname = $_POST['new_real_name'];
	}
	if (empty($_POST['employer'])) {
		$employer = $account['Employer'];
	} else {
		if ($account['Employer'] != $_POST['employer']) {
			$error_msg .= 'Employer successfully updated.<br/>';
		}
		$employer = $_POST['employer'];
	}
	$error_msg = empty($error_msg) ? '' : substr($error_msg, 0, -5);
	$hide_crash = true;
	if (!update_account($_GET['user'], $new_uname, $new_rname, $new_phash, $employer)) {
		$error_msg = 'Error: the specified username is already taken.';
	}
}
?><!doctype html>
<html lang="en">
  <head>
    <?php require_once('./inc/admin/blocks/head.inc.php'); ?>
  </head>
  <body>

    <?php require_once('./inc/admin/blocks/menu.inc.php'); ?>

	<div class="main-div">
	
	  <div id="error_box" class="alert alert-warning <?php if (empty($error_msg)) { echo 'd-none'; } ?>" role="alert">
	    <span id='error_msg'><?php if (!empty($error_msg)) echo $error_msg; ?></span>
	  </div>
	  
	  <h1>Account Details</h1>
	  
	  <?php
	  $account = get_account_byid($_GET['user']);
	  
	  if (!empty($account) && $account !== 'N/A') {
		$account = mysqli_fetch_assoc($account);
		$user_name = safe_str($account['UserName']);
		$real_name = safe_str($account['RealName']);
		$created = safe_str($account['Created']);
		$last_time = empty($account['LastTime']) ? 'never' : safe_str($account['LastTime']);
		$last_ip = empty($account['LastIP']) ? 'n/a' : safe_str($account['LastIP']);
		$employer = empty($account['Employer']) ? 'n/a' : safe_str($account['Employer']);
		echo "<h5>$real_name ($user_name)</h5>";
		echo "<p>Employer: $employer<br/>";
		echo "Created: $created<br/>";
		echo "Last Login: $last_time<br/>";
		echo "Last IP: $last_ip</p>";
	  ?>
	  
	  <h2>Edit Account</h2>
	  
	  <form class="card p-2" id="edit_user_form" method="post" action="">
        <label for="employer">Change Employer:</label>
		<select class="custom-select d-block w-100" name="employer" id="employer" required>
		  <?php
		  $selected = false;
		  $options = '';
		  foreach ($employers as $emkey => $emval) {
			  if ($account['Employer'] == $emval) {
				  $sel_txt = 'selected';
				  $selected = true;
			  } else {
				  $sel_txt = '';
			  }
			  $options .= "<option value='$emval' $sel_txt>$emval</option>";
		  }
		  
		  $sel_txt = $selected ? '' : 'selected';
		  echo "<option value='' $sel_txt>Choose ...</option>$options";
		  ?>
        </select>
        <label for="new_user_name" id="uname_label" style="margin-top:10px">Change User Name:</label>
		<input type="text" name="new_user_name" id="new_user_name" class="form-control w-100" placeholder="<?php echo $account['UserName']; ?>" maxlength="50" data-toggle="user_popover" title="Empty Form" data-content="Nothing has been changed.">
        <label for="new_real_name" id="rname_label" style="margin-top:10px">Change Real Name:</label>
		<input type="text" name="new_real_name" id="new_real_name" class="form-control w-100" placeholder="<?php echo $account['RealName']; ?>" maxlength="50">
        <label for="new_pass_txt" id="pass_label" style="margin-top:10px">New Password:</label>
		<input type="password" name="new_pass_txt" id="new_pass_txt" class="form-control w-100" placeholder="Password" maxlength="99">
        <label for="prep_txt" id="repeat_label" style="margin-top:10px">Repeat Password:</label>
		<input type="password" name="prep_txt" id="prep_txt" class="form-control w-100" placeholder="Password" maxlength="99" data-toggle="prep_popover" title="Incorrect Value" data-content="The passwords do not match.">
		<hr class="mb-3" />
		<div class="container">
		  <div class="row">
			<div class="col-12 col-md-6">
			  <button type="button" class="btn btn-primary w-100" onclick="window.history.go(-1); return false;">Go Back</button> 
			</div>
			<div class="col-12 col-md-6">
			  <button type="button" class="btn btn-primary w-100" id="submit_btn">Submit</button>
			</div>
		  </div>
		</div>
	  </form>
	  
	  <?php
	  } else {
		echo '<p>Unable to find account in database.</p>';
	  }
	  ?>
	  
	</div>

	<script>
	function pass_valid() {
		if ($("#new_pass_txt").val() == '' && $("#prep_txt").val() == '') {
			if ($("#employer").val() == '' && $("#new_user_name").val() == '' && $("#new_real_name").val() == '') {
				$('#new_user_name').popover('show');
				return false;
			} else {
				return true;
			}
		} else if ($("#new_pass_txt").val() != $('#prep_txt').val()) {
			$('#prep_txt').popover('show');
			return false;
		} else {
			return true;
		}
	}

	$(document).ready(function() {
		$('[data-toggle="user_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="prep_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$("#employer").on("change", function() {
			$("#new_user_name").popover('hide');
		});
		$("#new_user_name").on("change paste keyup", function() {
			$(this).popover('hide');
		});
		$("#new_real_name").on("change paste keyup", function() {
			$("#new_user_name").popover('hide');
		});
		$("#new_pass_txt").on("change paste keyup", function() {
			if ($("#new_pass_txt").val() == $("#prep_txt").val()) {
				$("#prep_txt").popover('hide');
			}
			$("#new_user_name").popover('hide');
		});
		$("#prep_txt").on("change paste keyup", function() {
			if ($("#new_pass_txt").val() == $("#prep_txt").val()) {
				$(this).popover('hide');
			}
			$("#new_user_name").popover('hide');
		});
		$("#submit_btn").click(function(){
			if (pass_valid()) {
				$(this).addClass('disabled');
				$('#edit_user_form').submit();
			}
		});
	});
	</script>
  </body>
</html>