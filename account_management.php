<?php
	$pageTitle = 'Account Management';
    require_once('head.php');

    $for_user = $USER;
    if ((isset($_REQUEST['user'])) && ($_REQUEST['user'] != $USER->user_id)) {
        if ($USER->flag_is_system_admin) {
            $for_user = User::getOneFromDb(['user_id'=>$_REQUEST['user']],$DB);
            $for_user->loadInstGroups();
            $for_user->loadEqGroups();
        }
        else {
            util_redirectToAppHome('failure',53);
        }
    }
?>


	<form action="" id="" class="form-horizontal" name="" method="">
		<legend>Account Management</legend>
		<div class="control-group">
			<label class="control-label" for="accountName">Name</label>

			<div class="controls">
				<?php echo $for_user->fname . ' ' . $for_user->lname; ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountUsername">Username</label>

			<div class="controls">
				<?php echo $for_user->username; ?>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountEmail">Email</label>

			<div class="controls">
				<a href="mailto:<?php echo $for_user->email; ?>"><?php echo $for_user->email; ?></a>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountAdvisor">Advisor(s)</label>

			<div class="controls">
				<input type="text" id="accountAdvisor" value="<?php echo $for_user->advisor; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="accountNotesPublic">Notes (public)</label>

			<div class="controls">
				<textarea id="accountNotesPublic" class="notes-editing-region"><?php echo $for_user->notes; ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="institutionInfo">Institution Membership</label>

			<div class="controls">
				<ul class="unstyled" id="institutionInfo">
					<?php
					foreach ($for_user->inst_groups as $ig) {
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
                    $USER->loadCommPrefs();
                    if (count($for_user->eq_groups) > 0) {
                        foreach ($for_user->eq_groups as $ueg) {
                            echo EqGroup::listItemTag();
                            echo $ueg->toHTML();
//                            echo '<div class="view-control">'.$USER->comm_prefs[$ueg->eq_group_id]->toHTML()."</div>\n";
                            if (! array_key_exists($ueg->eq_group_id,$USER->comm_prefs)) {
                                // TODO: create new comm prefs for this group
                                // then re-load the comm prefs and/or adjust them to account for the new preference
                                echo '<div><b>TO DO: handle missing comm pref</b></div>';
                            }
                            else {

                                echo '<div>'.$USER->comm_prefs[$ueg->eq_group_id]->toHTMLForm(($ueg->permission &&
                                                                                               $ueg->permission->role &&
                                                                                               ($ueg->permission->role->priority == 1))).
                                    "</div>\n";
                            }
                            echo "</li>\n";
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
                <ul id="equipmentGroups">
                    <?php
                    $for_user->loadSchedules();
                    if (count($for_user->schedules) > 0) {
                        foreach ($for_user->schedules as $sched) {
                            echo $sched->toListItemLinked();
                        }
                    }
                    else {
                        echo "<li>You do not have anything reserved.</li>";
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