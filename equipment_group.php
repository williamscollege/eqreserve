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
		$is_group_access   = FALSE;
		$is_group_manager  = FALSE;

		if ($USER->flag_is_system_admin) {
			$is_group_access   = TRUE;
			$is_group_manager  = TRUE;
			$Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => $eid], $DB);
		}
		else {
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
		}

		// security: redirect if does not belong here
		if (!$is_group_access) {
			util_redirectToAppHome('failure', 50);
		}

		?>
		<script type="text/javascript" xmlns="http://www.w3.org/1999/html">
			$(document).ready(function () {
				// default conditions

				// ***************************
				// Listeners
				// ***************************

//				// Show ajax form
//				$("#btnDisplayEditEqGroup").click(function () {
//					$("#btnDisplayEditEqGroup").addClass('hide');
//					$("#eqGroupFields").removeClass('hide');
//				});

				// Toggle Manager View (text or editable form)
				$("#toggleManagerOptions").click(function () {
					$("#managerView, #managerEdit").toggleClass("hide");
				});

				// Update form values
				$("#goStartMinute").change(function () {
					$("#startMinute").val($("#goStartMinute").val());
				});
				$("#goMinDurationMinutes").change(function () {
					$("#minDurationMinutes").val($("#goMinDurationMinutes").val());
				});
				$("#goMaxDurationMinutes").change(function () {
					$("#maxDurationMinutes").val($("#goMaxDurationMinutes").val());
				});
				$("#goDurationIntervalMinutes").change(function () {
					$("#durationIntervalMinutes").val($("#goDurationIntervalMinutes").val());
				});

				// custom form cleanup
				$("#btnCancelEditEqGroup").click(function () {
					cleanUpForm("formEditEqGroup")
					// hide form
					$("#toggleManagerOptions").click(function () {
						$("#managerView, #managerEdit").toggleClass("hide");
					});
				});

				// Remove later: debugging jquery validator plugin
				$("a.check").click(function () {
					alert("Valid: " + $("#formEditEqGroup").valid());
					return false;
				});

				// reset altered button
				$("#formEditEqGroup INPUT[type='text'], #formEditEqGroup TEXTAREA").change(function () {
					$("#btnSubmitEditEqGroup").removeClass('btn-link').text('Save');
				})


				// ***************************
				// Form validation
				// ***************************
				var validator = $('#formEditEqGroup').validate({
					rules: {
						groupName: {
							minlength: 2,
							required: true
						},
						groupDescription: {
							minlength: 2,
							required: true
						},
						startMinute: {
							/* TODO: CSV List: strip spaces, ensure only integers and commas */
							required: true
						},
						minDurationMinutes: {
							digits: true,
							required: true
						},
						maxDurationMinutes: {
							digits: true,
							required: true
						},
						durationIntervalMinutes: {
							digits: true,
							required: true
						}
					},
					highlight: function (element) {
						$(element).closest('.control-group').removeClass('success').addClass('error');
					},
//					success: function (element) {
//						element
//							.text('OK!').addClass('valid')
//							.closest('.control-group').removeClass('error').addClass('success');
//					},
					submitHandler: function (form) {
						var url = $("#formEditEqGroup").attr('action');
						var formName = "formEditEqGroup";
//							alert('url=' + url + '\n' + 'formName=' + formName + '\n');

						$.ajax({
							type: 'POST',
							url: url,
							data: {
								ajaxVal_GroupID: $('#eqGroupID').val(),
								ajaxVal_GroupName: $('#groupName').val(),
								ajaxVal_GroupDescription: $('#groupDescription').val(),
								ajaxVal_StartMinute: $('#startMinute').val(),
								ajaxVal_MinDurationMinute: $('#minDurationMinutes').val(),
								ajaxVal_MaxDurationMinute: $('#maxDurationMinutes').val(),
								ajaxVal_DurationIntervalMinutes: $('#durationIntervalMinutes').val()
							},
							dataType: 'html',
							success: function (data) {
								// reset form
								cleanUpForm("formEditEqGroup")

								if (data) {
									// document.write(data);
									// create visual indicator to show success
									$("#maxDurationMinutes").val(ajaxVal_MaxDurationMinute);
									$("#btnSubmitEditEqGroup").addClass('btn-link').text('Saved!');
								}
								else {
									// show error
									$("#btnSubmitEditEqGroup").append('<p><span class="label label-important">Important</span> An error occurred!</p>');
								}
							}
						});

					}
				})


				// ***************************
				// Custom functions
				// ***************************
				function cleanUpForm(formName) {
					// reset form
					$("#" + formName).trigger("reset");
					validator.resetForm();
					// manually remove input highlights
					$(".control-group").removeClass('success').removeClass('error');
				}

			});
		</script>

		Remove this later: <a href="#" class="check">is form valid?</a><br>
		<?php

		# admin or manager: is allowed to edit fields
		if ($USER->flag_is_system_admin || $is_group_manager) {
			# TODO: cancel button will revert FORM back to printed output
			?>
			<a href="#" id="toggleManagerOptions" class="btn btn-mini btn-inverse"><i class="icon-white icon-pencil"></i> Manager: Edit Form</a>
			<div id="managerEdit" class="hide">
				<form action="ajax_edit_eq_group.php" id="formEditEqGroup" class="form-horizontal" name="formEditEqGroup" method="post">
					<input type="hidden" id="eqGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />

					<div id="eqGroupFields">
						<legend>Equipment Group: <?php echo $Requested_EqGroup->name; ?></legend>
						<div class="control-group">
							<label class="control-label" for="groupName">Name</label>

							<div class="controls">
								<input type="text" id="groupName" class="input-large" name="groupName" value="<?php echo $Requested_EqGroup->name; ?>" placeholder="Name of group" maxlength="200" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="groupDescription">Description</label>

							<div class="controls">
								<textarea rows="3" id="groupDescription" class="input-large" name="groupDescription" placeholder="Description of group"><?php echo $Requested_EqGroup->descr; ?></textarea>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="groupManagers">Managed by</label>

							<div class="controls">
								<input type="text" id="groupManagers" class="input-large" disabled="disabled" name="groupManagers" value="<?php #echo $Requested_EqGroup->name; ?>" placeholder="Managed by" maxlength="200" />
							</div>
						</div>

						<legend>Reservation Rules</legend>

						<div class="control-group">
							<label class="control-label" for="goStartMinute">Start time (minutes)</label>

							<div class="controls">
								<?php
								$defaultStartMinute = [
									""           => "Select to Edit",
									"00"         => "hourly (00)",
									"0,30"       => "half hours (00,30)",
									"0,15,30,45" => "quarter hours (00,15,30,45)"
								];
								?>
								<select id="goStartMinute" class="span2">
									<?php
									foreach ($defaultStartMinute as $key => $val) {
										echo "<option value=\"$key\">$val</option>\n";
									}
									?>
								</select>
								<i class="icon-arrow-right"></i>
								<input type="text" id="startMinute" class="input-small" name="startMinute" value="<?php echo $Requested_EqGroup->start_minute; ?>" placeholder="Start time (w/ commas)" maxlength="200" />
								Reservations must start and end on one of these minutes of the hour.
							</div>
						</div>
						<?php
						$defaultDuration = [
							""    => "Select to Edit",
							15    => "15 minutes",
							30    => "30 minutes",
							45    => "45 minutes",
							60    => "1 hour",
							120   => "2 hours",
							240   => "4 hours",
							480   => "8 hours",
							960   => "16 hours",
							1440  => "24 hours",
							2880  => "2 days",
							10080 => "1 week",
							20160 => "2 weeks",
							80640 => "4 weeks"
						];
						?>
						<div class="control-group">
							<label class="control-label" for="goMinDurationMinutes">Min duration (minutes)</label>

							<div class="controls">
								<select id="goMinDurationMinutes" class="span2">
									<?php
									foreach ($defaultDuration as $key => $val) {
										echo "<option value=\"$key\">$val</option>\n";
									}
									?>
								</select>
								<i class="icon-arrow-right"></i>
								<input type="text" id="minDurationMinutes" class="input-mini" name="minDurationMinutes" value="<?php echo $Requested_EqGroup->min_duration_minutes; ?>" placeholder="Duration" maxlength="6" />
								The minimum length of time that can be reserved.
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="goMaxDurationMinutes">Max duration (minutes)</label>

							<div class="controls">
								<select id="goMaxDurationMinutes" class="span2">
									<?php
									foreach ($defaultDuration as $key => $val) {
										echo "<option value=\"$key\">$val</option>\n";
									}
									?>
								</select>
								<i class="icon-arrow-right"></i>
								<input type="text" id="maxDurationMinutes" class="input-mini" name="maxDurationMinutes" value="<?php echo $Requested_EqGroup->max_duration_minutes; ?>" placeholder="Duration" maxlength="6" />
								The maximum length of time that can be reserved.
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="goDurationIntervalMinutes">Duration unit (minutes)</label>

							<div class="controls">
								<select id="goDurationIntervalMinutes" class="span2">
									<?php
									foreach ($defaultDuration as $key => $val) {
										echo "<option value=\"$key\">$val</option>\n";
									}
									?>
								</select>
								<i class="icon-arrow-right"></i>
								<input type="text" id="durationIntervalMinutes" class="input-mini" name="durationIntervalMinutes" value="<?php echo $Requested_EqGroup->duration_chunk_minutes; ?>" placeholder="Duration" maxlength="6" />
								The interval unit duration of time that can be reserved.
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="btnSubmitEditEqGroup"></label>

							<div class="controls">
								<button type="submit" id="btnSubmitEditEqGroup" class="btn btn-success">Save</button>
								<button type="reset" id="btnCancelEditEqGroup" class="btn btn-link btn-cancel">Cancel</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		<?php
		} # end: output toggle form for: manager or admin
		//		echo "<pre>Requested_EqGroup:"; print_r($Requested_EqGroup); echo "</pre>";
		//		echo "<pre>USER:"; print_r($USER); echo "</pre>";

		# Show this to all authenticated users
		echo "<div id=\"managerView\">\n";
		echo "<h2>Equipment Group: " . $Requested_EqGroup->name . "</h2>\n";
		echo "<p>Description: " . $Requested_EqGroup->descr . "</p>\n";
		echo "<p>Managed by: [list here]</p>\n";
		echo "<h2>Reservation Rules</h2>\n";
		echo "Start time (minutes): <span class=\"label label-inverse\">" . $Requested_EqGroup->start_minute . "</span> Reservations must start and end on one of these minutes of the hour.<br />\n";
		echo "Min duration (minutes): <span class=\"label label-inverse\">" . $Requested_EqGroup->min_duration_minutes . "</span> The minimum length of time that can be reserved.<br />\n";
		echo "Max duration (minutes): <span class=\"label label-inverse\">" . $Requested_EqGroup->max_duration_minutes . "</span> The maximum length of time that can be reserved.<br />\n";
		echo "Duration unit (minutes): <span class=\"label label-inverse\">" . $Requested_EqGroup->duration_chunk_minutes . "</span> The interval unit duration of time that can be reserved.<br />\n";
		echo "</div>";
		?>

		<br />
		<h2>Reserve Equipment</h2>
		<form action="reservation.php" id="formReservation" class="form-horizontal" name="formReservation" method="post">
			<input type="hidden" id="resGroupID" value="" />

			<div id="resGroupFields">
				<legend>Reserve Equipment:</legend>
				<div class="control-group">
					<label class="control-label" for=""></label>

					<div class="controls">

					</div>
				</div>

			</div>
		</form>
	<?php
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