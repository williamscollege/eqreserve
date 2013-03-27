<?php
	$pageTitle = 'Edit Equipment Group';
	require_once('head.php');
	require_once('/classes/eq_group.class.php');


	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated

		// fetch querystring
		$eid = $_REQUEST["eid"];

		// declare variables
		$Requested_EqGroup = [];
		$is_group_access  = FALSE;
		$is_group_manager = FALSE;

		// does user have permission to access this group?
		$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);
		foreach ($UserEqGroups as $ueg) {
			if ($ueg->permission->eq_group_id == $eid) {
				// set flag: is this allowed to access this group?
				$is_group_access = TRUE;

				// create group object for easier manipulation
				$Requested_EqGroup = $ueg;

				// set flag: is group manager?
				if ($Requested_EqGroup->permission->role_id == 1) {
					$is_group_manager = TRUE;
				}
			}
		}

		// security: redirect if does not belong here
		if (!$is_group_access) {
			util_redirectToAppHome(50);
		}

		# admin or manager: is allowed to edit fields
		if ($USER->flag_is_system_admin || $is_group_manager) {
			?>
			<form action="ajax_add_eq_group.php" id="formAddEqGroup" class="form-inline" name="formAddEqGroup" method="post">
				<div id="eqGroupFields">
					<legend><?php echo $Requested_EqGroup->name; ?></legend>
					<div class="control-group">
						<label class="control-label" for="eqGroupName">Name</label>

						<div class="controls">
							<input type="text" id="eqGroupName" class="input-large" name="eqGroupName" value="<?php echo $Requested_EqGroup->name; ?>" placeholder="Name of group" maxlength="200" />
							<button type="submit" id="btnSubmitEditEqGroupName" class="btn btn-success">Edit</button>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="eqGroupDescription">Description</label>

						<div class="controls">
							<textarea rows="3" id="eqGroupDescription" class="input-large" name="eqGroupDescription" placeholder="Description of group"><?php echo $Requested_EqGroup->descr; ?></textarea>
							<button type="submit" id="btnSubmitEditEqGroupDescription" class="btn btn-success">Edit</button>
						</div>
					</div>
				</div>
			</form>
		<?php
		}
		else {
			echo "<h2>" . $Requested_EqGroup->name . "</h2>";
			echo "<p>" . $Requested_EqGroup->descr . "</p>";
		}

//		echo "<pre>TESTING:";
//		print_r($Requested_EqGroup);
//		echo "</pre>";
//
//		echo "<pre>";
//		print_r($USER);
//		echo "</pre>";
	}
	else {
		// SECTION: not yet authenticated, wants to log in
		?>
		<div class="hero-unit">
			<h2><?php echo LANG_INSTITUTION_NAME; ?></h2>

			<h1><?php echo LANG_APP_NAME; ?></h1>

			<br />

			<p>This is our system for scheduling equipment reservations.</p>

			<p>To sign in, please use your Williams username and password.</p>

		</div>
	<?php
	}

	require_once('foot.php');
?>