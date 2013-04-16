<?php
	$pageTitle = 'Account Management';
	require_once('head.php');
?>


	<form action="" id="" class="form-horizontal" name="" method="">
		<legend>Account Management</legend>
		<div class="control-group">
			<label class="control-label" for="accountName">Name</label>

			<div class="controls">
				<input type="text" disabled="disabled" id="accountName" value="<?php echo $USER->fname . ' ' . $USER->lname; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountUsername">Username</label>

			<div class="controls">
				<input type="text" disabled="disabled" id="accountUsername" value="<?php echo $USER->username; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountEmail">Email</label>

			<div class="controls">
				<input type="text" disabled="disabled" id="accountEmail" class="input-xlarge" value="<?php echo $USER->email; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountAdvisor">Advisor</label>

			<div class="controls">
				<input type="text" disabled="disabled" id="accountAdvisor" value="<?php echo $USER->advisor; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountNotesPublic">Notes (public)</label>

			<div class="controls">
				<input type="text" disabled="disabled" id="accountNotesPublic" class="input-xxlarge" value="<?php echo $USER->notes; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="institutionInfo">Institution Membership</label>

			<div class="controls">
				<ul class="unstyled" id="institutionInfo">
					<?php
					foreach ($USER->inst_groups as $ig) {
						//echo "<input type=\"text\" disabled=\"disabled\" value=\"" . $ig->name . "\" /><br/>\n";
						echo $ig->toListItemLinked()."\n";
					}
					?>
				</ul>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="equipmentGroups">Equipment Groups</label>

			<div class="controls">
				<ul class="unstyled" id="equipmentGroups">
					<?php
                    if (count($USER->eq_groups) > 0) {
                        foreach ($USER->eq_groups as $ueg) {
                            echo $ueg->toListItemLinked();
                        }
                    }
                    else {
                        echo "<li>You do not have access to any equipment groups.</li>";
                    }
					?>
				</ul>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="reservations">Reservations</label>

			<div class="controls">
                <ul class="unstyled" id="equipmentGroups">
                    <?php
                    $USER->loadReservations();
                    if (count($USER->reservations) > 0) {
                        foreach ($USER->reservations as $resv) {
                            echo $resv->toListItemLinked();
                        }
                    }
                    else {
                        echo "<li>You do not have any reservations.</li>";
                    }
                    ?>
                </ul>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="btnSubmitEditAccount"></label>
			<!--
					<div class="controls">
						<button type="submit" id="btnSubmitEditAccount" class="btn btn-success">Edit Account</button>
						<button type="reset" id="btnCancelEditAccount" class="btn btn-link btn-cancel">Cancel</button>
					</div>
			-->
		</div>
	</form>


<?php
	require_once('foot.php');
?>