<?php admin_valid(); 

if (!empty($_POST['new_user_name']) && !empty($_POST['new_pass_txt'])) {
	$hide_crash = true;
	$pass_hash = pass_hash($_POST['new_pass_txt'], $hash_rounds);
	if (create_account($_POST['new_user_name'], $_POST['real_name'], $pass_hash, $_POST['employer'])) {
		redirect('./admin.php?page=home');
	} else {
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
	    <span id='error_msg'><?php if (!empty($error_msg)) safe_echo($error_msg); ?></span>
	  </div>
	  
	  <h1>Create New Account</h1>
	  
	  <form class="card p-2" id="new_user_form" method="post" action="">
        <label for="employer">Employer:</label>
		<select class="custom-select d-block w-100" name="employer" id="employer" required>
		  <?php
		  foreach ($employers as $emkey => $emval) {
			  $sel_txt = ($emkey == 0) ? 'selected' : '';
			  echo "<option value='$emval' $sel_txt>$emval</option>";
		  }
		  ?>
        </select>
        <label for="new_user_name" id="uname_label" style="margin-top:10px">User Name:</label>
		<input type="text" name="new_user_name" id="new_user_name" class="form-control w-100" data-toggle="uname_popover" title="Invalid Value" placeholder="jsmith" maxlength="50" data-content="This field cannot be empty." required>
        <label for="real_name" id="rname_label" style="margin-top:10px">Real Name:</label>
		<input type="text" name="real_name" id="real_name" class="form-control w-100" data-toggle="rname_popover" title="Invalid Value" placeholder="John Smith" maxlength="50" data-content="This field cannot be empty." required>
        <label for="new_pass_txt" id="pass_label" style="margin-top:10px">Password:</label>
		<input type="password" name="new_pass_txt" id="new_pass_txt" class="form-control w-100" placeholder="Password" maxlength="99" data-toggle="pass_popover" title="Invalid Value" data-content="This field cannot be empty." required>
        <label for="prep_txt" id="repeat_label" style="margin-top:10px">Repeat Password:</label>
		<input type="password" name="prep_txt" id="prep_txt" class="form-control w-100" placeholder="Password" maxlength="99" data-toggle="prep_popover" title="Incorrect Value" data-content="The passwords do not match." required>
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
	  
	</div>

	<script>
	function user_valid() {
		if ($("#new_user_name").val() == '') {
			$('#new_user_name').popover('show');
			return false;
		} else if ($("#real_name").val() == '') {
			$('#real_name').popover('show');
			return false;
		} else if ($("#new_pass_txt").val() == '') {
			$('#new_pass_txt').popover('show');
			return false;
		} else if ($("#new_pass_txt").val() != $('#prep_txt').val()) {
			$('#prep_txt').popover('show');
			return false;
		} else {
			return true;
		}
	}

	$(document).ready(function() {
		$('[data-toggle="uname_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="rname_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="pass_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="prep_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$("#new_user_name").on("change paste keyup", function() {
			if ($("#new_user_name").val() != '') {
				$(this).popover('hide');
			}
		});
		$("#real_name").on("change paste keyup", function() {
			if ($("#real_name").val() != '') {
				$(this).popover('hide');
			}
		});
		$("#new_pass_txt").on("change paste keyup", function() {
			if ($("#new_pass_txt").val() != '') {
				$(this).popover('hide');
			}
			if ($("#new_pass_txt").val() == $("#prep_txt").val()) {
				$("#prep_txt").popover('hide');
			}
		});
		$("#prep_txt").on("change paste keyup", function() {
			if ($("#new_pass_txt").val() == $("#prep_txt").val()) {
				$(this).popover('hide');
			}
		});
		$("#submit_btn").click(function(){
			if (user_valid()) {
				$(this).addClass('disabled');
				$('#new_user_form').submit();
			}
		});
	});
	</script>
  </body>
</html>