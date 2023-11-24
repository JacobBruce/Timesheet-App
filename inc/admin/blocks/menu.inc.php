	<div class="menu-div">
	  <ul class="nav flex-column">
	    <li class="nav-item">
		  <a class="nav-link<?php if ($page == 'home') echo ' active'; ?>" href="./admin.php?page=home">Manage Users</a>
	    </li>
	    <li class="nav-item">
		  <a class="nav-link<?php if ($page == 'sites') echo ' active'; ?>" href="./admin.php?page=sites">Manage Sites</a>
	    </li>
	    <li class="nav-item">
		  <a class="nav-link<?php if ($page == 'export') echo ' active'; ?>" href="./admin.php?page=export">Export</a>
	    </li>
	    <li class="nav-item">
		  <a class="nav-link<?php if ($page == 'settings') echo ' active'; ?>" href="./admin.php?page=settings">Settings</a>
	    </li>
	    <li class="nav-item">
		  <a class="nav-link" href="./admin.php?page=logout">Logout</a>
	    </li>
	  </ul>
	</div>