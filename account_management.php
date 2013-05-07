<?php
	$pageTitle = 'Account Management';
	require_once('head.php');

	$for_user = $USER;
	if ((isset($_REQUEST['user'])) && ($_REQUEST['user'] != $USER->user_id)) {
		if ($USER->flag_is_system_admin) {
			$for_user = User::getOneFromDb(['user_id' => $_REQUEST['user']], $DB);
			$for_user->loadInstGroups();
			$for_user->loadEqGroups();
		}
		else {
			util_redirectToAppHome('failure', 53);
		}
	}
?>
	<script type="text/javascript">
		$(document).ready(function () {
			$(".comm_pref-checkbox").change(function () {
				var comm_pref_id = $(this).attr('data-for-comm-pref');
				var pref_type = $(this).attr('data-comm-pref-type');
				var pref_state = $(this).is(':checked');
				var cached_this = this;
				var pref_action = 'setReminder';
				if (pref_type == 'alert_create') {
					pref_action = 'setAlertCreate';
				}
				else if (pref_type == 'alert_cancel') {
					pref_action = 'setAlertCancel';
				}

				//alert('TODO: handle pref set for pref:'+comm_pref_id+' of type:'+pref_type+' set value:'+pref_state);
				eqrUtil_setTransientAlert('progress', 'saving...');
				$.ajax({
					url: 'ajax_comm_pref.php',
					dataType: 'json',
					data: {'comm_pref': comm_pref_id,
						'commPrefAction': pref_action,
						'actionVal': pref_state
					}
				})
					.done(function (data, status, xhr) {
						if (data.status == 'success') {
							eqrUtil_setTransientAlert('success', 'saved');
						}
						else {
							eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
							$(cached_this).prop('checked', !pref_state);
						}
					})
					.fail(function (data, status, xhr) {
						eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
						$(cached_this).prop('checked', !pref_state);
					})
				;
			});

		});
	</script>


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
							echo $ig->toListItemLinked() . "\n";
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
								$isManager = ($ueg->permission && $ueg->permission->role && ($ueg->permission->role->priority == 1));
								echo EqGroup::listItemTag();
								echo $ueg->toHTML();
								//                            echo '<div class="view-control">'.$USER->comm_prefs[$ueg->eq_group_id]->toHTML()."</div>\n";
								if (!array_key_exists($ueg->eq_group_id, $USER->comm_prefs)) {
									// TODO: create new comm prefs for this group
									// then re-load the comm prefs and/or adjust them to account for the new preference

									$new_cp = new CommPref(['user_id'                            => $USER->user_id,
															'eq_group_id'                        => $ueg->eq_group_id,
															'flag_alert_on_upcoming_reservation' => TRUE,
															'flag_contact_on_reserve_create'     => $isManager,
															'flag_contact_on_reserve_cancel'     => $isManager,
															'DB'                                 => $DB]);

									$new_cp->updateDb();
									if (!$new_cp->matchesDb) {
										echo '<div class="text-error"><b>failed to create initial communicaiton preferences for ' . $ueg->name . '</b></div>';
									}
									else {
										$USER->comm_prefs[$ueg->eq_group_id] = $new_cp;
									}
								}
								echo '<div>' . $USER->comm_prefs[$ueg->eq_group_id]->toHTMLForm($isManager) .
									"</div>\n";
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