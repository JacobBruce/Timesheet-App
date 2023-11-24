<?php admin_valid();

if (isset($_POST['login_msg'])) {
  file_put_contents('inc/login_msg.txt', $_POST['login_msg']);
  $msg_set = true;
}
?><!doctype html>
<html lang="en">
  <head>
    <?php require_once('./inc/admin/blocks/head.inc.php'); ?>
  </head>
  <body>

    <?php require_once('./inc/admin/blocks/menu.inc.php'); ?>

	<div class="main-div">
	  <h1>Settings</h1>
	  <hr />
	  <?php
	  if (isset($msg_set)) {
	      echo '<div class="alert alert-success" role="alert">Login message successfully updated.</div>';
	  }
	  $login_msg = file_get_contents('inc/login_msg.txt');
	  ?>
      <form name="lmsg_form" id="lmsg_form" method="post" action="">
        <label for="login_msg">Login Message:</label>
        <textarea name="login_msg" class="form-control" maxlength="999" autofocus><?php echo $login_msg; ?></textarea>
        <h3 class="h3 mb-3 font-weight-normal"></h3>
        <button class="btn btn-primary" type="submit">Update</button>
      </form>
	  
	</div>

  </body>
</html>