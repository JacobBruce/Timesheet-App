<?php admin_valid(); ?><!doctype html>
<html lang="en">
  <head>
    <?php require_once('./inc/admin/blocks/head.inc.php'); ?>
  </head>
  <body>

    <?php require_once('./inc/admin/blocks/menu.inc.php'); ?>

	<div class="main-div">
	  <a class="btn btn-lg btn-primary" style="float:right" href="./admin.php?page=newuser">Add New User</a>
	  
	  <h1>User List</h1>
	  
	  <ul class="list-group user_list">
	    <?php
	    $accounts = list_all_accounts();
	    while ($row = mysqli_fetch_assoc($accounts)) {
	    ?>
		  <li class="list-group-item <?php if ($row['Disabled']) { echo "list-group-item-secondary"; } ?>"><?php safe_echo($row['RealName'].' ('.$row['UserName'].')'); ?> <span style="float:right;text-align:right;">
		    <?php if ($row['IsAdmin'] == 0) { ?><a href='./admin.php?page=timesheet&amp;user=<?php echo $row['AccountID']; ?>'>Timesheet</a> | <?php } ?>
		    <a href='./admin.php?page=details&amp;user=<?php echo $row['AccountID']; ?>'>Details</a> <?php if ($row['IsAdmin'] == 0) { ?>| <?php if ($row['Disabled']) { ?>
		    <a href='./admin.php?page=enable&amp;user=<?php echo $row['AccountID']; ?>'>Enable</a></li><?php } else { ?>
		    <a href='./admin.php?page=disable&amp;user=<?php echo $row['AccountID']; ?>'>Disable</a></li>
		    <?php } } ?>
		  </span>
	    <?php } ?>
	  </ul>
	  
	</div>

  </body>
</html>