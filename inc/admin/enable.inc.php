<?php admin_valid(); 

if (isset($_GET['confirm']) && !empty($_GET['user'])) {
	if (enable_account($_GET['user'])) {
		redirect('./admin.php?page=home');
	} else {
		echo '<p>An unexpected error occurred. Refresh the page and try again.</p>';
	}
	exit;
}
?><!doctype html>
<html lang="en">
  <head>
    <?php require_once('./inc/admin/blocks/head.inc.php'); ?>
  </head>
  <body>

    <?php require_once('./inc/admin/blocks/menu.inc.php'); ?>

	<div class="main-div">
	  <h1>Enable User Account</h1>
	  
	  <p>Are you sure you want to enable this account?</p>
	  
	  <button class="btn btn-lg btn-primary" onclick="window.history.go(-1); return false;">Go Back</button> 
	  <a class="btn btn-lg btn-primary" href="./admin.php?page=enable&amp;user=<?php safe_echo($_GET['user']); ?>&amp;confirm">Confirm</a>
	  
	</div>

  </body>
</html>