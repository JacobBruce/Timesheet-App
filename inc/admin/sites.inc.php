<?php admin_valid(); ?><!doctype html>
<html lang="en">
  <head>
    <?php require_once('./inc/admin/blocks/head.inc.php'); ?>
  </head>
  <body>

    <?php require_once('./inc/admin/blocks/menu.inc.php'); ?>
	
	<div class="modal" id="edit_modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Edit Site Name</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<p style="text-align:left">Change site name to:</p>
			<input type="text" name="new_name" id="new_name" class="form-control" maxlength="64" style="max-width:200px"  data-toggle="edit_popover" title="Invalid Value" data-content="This field cannot be empty." required autofocus>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			<button type="button" class="btn btn-primary" id="update_btn" OnClick="update_site()">Save changes</button>
		  </div>
		</div>
	  </div>
	</div>
	
	<div class="modal" id="new_modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Add New Site</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<p style="text-align:left">Job site name:</p>
			<input type="text" name="site_name" id="site_name" class="form-control" maxlength="64" style="max-width:200px"  data-toggle="new_popover" title="Invalid Value" data-content="This field cannot be empty." required autofocus>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			<button type="button" class="btn btn-primary" id="submit_btn" OnClick="create_site()">Create Site</button>
		  </div>
		</div>
	  </div>
	</div>

	<div class="main-div">
	<?php
	  if (isset($_GET['enable']) && !empty($_GET['id'])) {
		enable_site($_GET['id']);
	  } elseif (isset($_GET['disable']) && !empty($_GET['id'])) {
		disable_site($_GET['id']);
	  }
	?>
	  <div id="error_box" class="alert alert-warning <?php if (empty($error_msg)) { echo 'd-none'; } ?>" role="alert">
	    <span id='error_msg'><?php if (!empty($error_msg)) safe_echo($error_msg); ?></span>
	  </div>
	  
	  <a class="btn btn-lg btn-primary" style="float:right" href="#" OnClick="show_new_modal()">Add New Site</a>
	  
	  <h1>Job Site List</h1>
	  
	  <ul class="list-group user_list">
	    <?php
	    $sites = get_sites();
	    while ($row = mysqli_fetch_assoc($sites)) {
	    ?>
		  <li class="list-group-item"><?php safe_echo($row['SiteName']); ?> <span style="float:right;text-align:right;">
		    <?php if ($row['SiteName'] != 'Other') { ?>
		    <a href='#' OnClick="edit_site(<?php echo $row['SiteID']; ?>)">Edit Name</a> | <?php if ($row['Enabled'] == 1) { ?>
		    <a href='./admin.php?page=sites&amp;id=<?php echo $row['SiteID']; ?>&amp;disable'>Disable</a></li><?php } else { ?>
			<a href='./admin.php?page=sites&amp;id=<?php echo $row['SiteID']; ?>&amp;enable'>Enable</a></li>
			<?php } } ?>
		  </span>
	    <?php } ?>
	  </ul>

	</div>
	
	<script>
	var SiteID = 0;

	function handle_update(response) {
		if (response == 'SUCCESS') {
			$('#error_box').show().removeClass('d-none').removeClass('alert-warning').addClass('alert-success');
			$('#error_msg').html('Site was successfully updated. Reloading ...');
			window.location.reload(true);
		} else {
			$('#error_box').show().removeClass('d-none').removeClass('alert-success').addClass('alert-warning');
			$('#error_msg').html(response);
		}
		$('#edit_modal').modal('hide');
		$('#update_btn').html('Save Changes');
		$('#update_btn').removeClass('disabled');
	}

	function handle_submit(response) {
		if (response == 'SUCCESS') {
			$('#error_box').show().removeClass('d-none').removeClass('alert-warning').addClass('alert-success');
			$('#error_msg').html('Successfully created new site. Reloading ...');
			window.location.reload(true);
		} else {
			$('#error_box').show().removeClass('d-none').removeClass('alert-success').addClass('alert-warning');
			$('#error_msg').html(response);
		}
		$('#new_modal').modal('hide');
		$('#submit_btn').html('Create Site');
		$('#submit_btn').removeClass('disabled');
	}

	function handle_error(response) {
		$('#error_box').show().removeClass('d-none').removeClass('alert-success').addClass('alert-warning');
		$('#error_msg').html(response);
		$('#edit_modal').modal('hide');
		$('#new_modal').modal('hide');
		$('#update_btn').html('Save Changes');
		$('#update_btn').removeClass('disabled');
		$('#submit_btn').html('Create Site');
		$('#submit_btn').removeClass('disabled');
	}
	
	function show_new_modal() {
		$('#new_modal').modal('show');
	}
	
	function edit_site(site_id) {
		SiteID = site_id;
		$('#edit_modal').modal('show');
	}
	
	function update_site() {
		if ($('#new_name').val() != '') {
			var newName = encodeURIComponent($('#new_name').val());
			$('#update_btn').addClass('disabled');
			ajax_post('./inc/jobs/update_site.inc.php', 
			'site_id='+SiteID+'&new_name='+newName, 
			handle_update, handle_error);
			$('#update_btn').addClass('disabled');
			$('#update_btn').html('Updating ...');
		} else {
			$('#new_name').popover('show');
		}
	}
	
	function create_site() {
		if ($('#site_name').val() != '') {
			var siteName = encodeURIComponent($('#site_name').val());
			$('#submit_btn').addClass('disabled');
			ajax_post('./inc/jobs/new_site.inc.php', 
			'site_name='+siteName, handle_submit, handle_error);
			$('#submit_btn').addClass('disabled');
			$('#submit_btn').html('Updating ...');
		} else {
			$('#site_name').popover('show');
		}
	}
	
	$(document).ready(function() {
		$('[data-toggle="edit_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$('[data-toggle="new_popover"]').popover({
			trigger: 'manual',
			container: 'body'
		});
		$("#new_name").on("change paste keyup", function() {
			if ($('#new_name') != '') {
				$(this).popover('hide');
			}
		});
		$("#site_name").on("change paste keyup", function() {
			if ($('#site_name') != '') {
				$(this).popover('hide');
			}
		});
		$('#edit_modal').on('shown.bs.modal', function () {
			$('#new_name').trigger('focus')
		});
		$('#new_modal').on('shown.bs.modal', function () {
			$('#site_name').trigger('focus')
		});
	});
	</script>

  </body>
</html>