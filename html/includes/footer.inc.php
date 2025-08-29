		</div>
		<div class="col-md-2 col-md-pull-10">
			<div class="sidebar-nav">
				<ul class="nav nav-pills nav-stacked">
					<li><a href="index.php">Main</a></li>
					<li><a href="user.php">User Information</a></li>
					<li><a href="user_bill.php">User Bill</a></li>
					<li><a href="user_graphs.php">User Graphs</a></li>
<!-- 					<li><a href="user_files.php">User Files</a></li> -->
					<?php
					if ( isset($login_user) && $login_user->is_admin() ){
						?>
					<hr>
					<li><a href="data_billing.php">Billing Report</a></li>
					<li><a href="stats_accumulated.php">Accumulated Stats</a></li>
					<li><a href="stats_monthly.php">Monthly Stats</a></li>
					<li><a href="stats_yearly.php">Yearly Stats</a></li>
					<li><a href="stats_fiscal.php">Fiscal Stats</a></li>
					<hr>
					<li><a href="list_directories.php">List Directories</a></li>
					<li><a href="list_users.php">List Users</a></li>
					<li><a href="add_user.php">Add User</a></li>
					<li><a href="add_directory.php">Add Directory</a></li>

					<li><a href="add_tokens.php">Pre-Pay</a></li>
<!--					<li><a href="list_transactions.php">Transaction List</a></li>
-->
					<li><a href="unwatched_dirs.php">Unmonitored Directories</a></li>
					<li><a href="settings.php">Settings</a></li>
						<?php	
					}
					?>
				</ul>
			</div>
		</div>
	</div>
	<div class='col-sm-12' style='text-align: center; padding: 15px 0'>
	<br><em>Computer & Network Resource Group - Carl R. Woese Institute for Genomic Biology</em>
	<br><em>If you have any questions, please email us at <a href='mailto:<?php echo ADMIN_EMAIL; ?>'><?php echo ADMIN_EMAIL; ?></a></em>
	<br><em><a target='_blank' href='https://www.igb.illinois.edu'>Carl R. Woese Institute for Genomic Biology Home Page</a></em>
	<br><em><a target='_blank' href='https://www.vpaa.uillinois.edu/resources/web_privacy'>University of Illinois System Web Privacy Notice</a> </em>
	<em>&copy; 2015-<?php echo date('Y'); ?> University of Illinois Board of Trustees</em>
	</div>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="includes/bootstrap/js/bootstrap.min.js"></script>
  </body>
</html>
