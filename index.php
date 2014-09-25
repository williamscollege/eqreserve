<?php
	$pageTitle = 'Home';
	require_once('head.php');
	require_once('/classes/eq_group.class.php');


	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated

		?>
		<script type="text/javascript">
			$(document).ready(function () {
				// default conditions

				// ***************************
				// Listeners
				// ***************************

				// Show ajax form
				$("#btnDisplayAddEqGroup").click(function () {
					$("#btnDisplayAddEqGroup").addClass('hide');
					$("#eqGroupFields").removeClass('hide');
				});

				// custom form cleanup
				$("#btnCancelAddEqGroup").click(function () {
					cleanUpForm("frmAddGroup");
				});


				// ***************************
				// Form validation
				// ***************************

				var validateAddGroup = $('#frmAddGroup').validate({
					rules: {
						groupName: {
							minlength: 2,
							required: true
						},
						groupDescription: {
							minlength: 2,
							required: true
						}
					},
					highlight: function (element) {
						$(element).closest('.control-group').removeClass('success').addClass('error');
					},
					success: function (element) {
						element
							.text('OK!').addClass('valid')
							.closest('.control-group').removeClass('error').addClass('success');
					},
					submitHandler: function (form) {
						// show loading text (button)
						$("#btnSubmitAddEqGroup").button('loading');

						var formName = $("#frmAddGroup").attr('name');		// get name from the form element
						var action = $('#' + formName + ' #ajaxGroupAction').val();
						var group_name = $('#' + formName + ' #groupName').val();
						var group_description = $('#' + formName + ' #groupDescription').val();
						// alert('formName=' + formName + '\n' + 'group_name=' + group_name + '\n' + 'group_description=' + group_description);

						$.ajax({
							type: 'GET',
							url: $("#frmAddGroup").attr('action'),
							data: {
								ajaxVal_Action: action,
								ajaxVal_Name: group_name,
								ajaxVal_Description: group_description
							},
							dataType: 'json',
							success: function (data) {
								// reset form
								cleanUpForm("frmAddGroup");

								if (data.status == 'success') {
									// remove error messages
									$('DIV.alert-error').remove();

									// update the element with new data from the ajax call
									$("UL#displayEqGroups").append(data.html_output);
								}
								else {
									// show error message
									$("UL#displayEqGroups").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
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
					validateAddGroup.resetForm();
					// hide form, show button to activate form
					$("#eqGroupFields").addClass('hide');
					$("#btnDisplayAddEqGroup").removeClass('hide');
					// manually remove input highlights
					$(".control-group").removeClass('success').removeClass('error');
					// reset button
					$("#btnSubmitAddEqGroup").button('reset');
				}

			});
		</script>


		<?php
		echo "<hr />";
		echo "<h3>Your Equipment Groups</h3>";
		echo "<ul class=\"unstyled\" id=\"displayEqGroups\">";

		# is system admin?
		if ($USER->flag_is_system_admin) {
			# get groups for this ordinary user
			$UserEqGroups = EqGroup::getAllEqGroupsForAdminUser($USER);
			if (count($UserEqGroups) > 0) {
				foreach ($UserEqGroups as $ueg) {
					echo "<li><a href=\"equipment_group.php?eid=" . $ueg->eq_group_id . "\" title=\"\">" . $ueg->name . "</a>: " . $ueg->descr . "</li>";
				}
			}
			else {
				echo "<li>You do not belong to any equipment groups.</li>";
			}
			echo "</ul>";
			# system admin may add new eq_groups
			?>

			<form action="ajax_actions/ajax_eq_group.php" id="frmAddGroup" class="form-horizontal" name="frmAddGroup" method="post">
				<button type="button" id="btnDisplayAddEqGroup" class="btn btn-primary" name="btnDisplayAddEqGroup">Add a new equipment group
				</button>

				<div id="eqGroupFields" class="hide">
					<legend>Add a new equipment group</legend>
					<div class="control-group">
						<label class="control-label" for="groupName">Name</label>

						<div class="controls">
							<input type="hidden" id="ajaxGroupAction" name="ajaxGroupAction" value="add-group" />
							<input type="text" id="groupName" class="input-large" name="groupName" value="" placeholder="Name of group" maxlength="200" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="groupDescription">Description</label>

						<div class="controls">
							<input type="text" id="groupDescription" class="input-xlarge" name="groupDescription" value="" placeholder="Description of group" maxlength="200" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="btnSubmitAddEqGroup"></label>

						<div class="controls">
							<button type="submit" id="btnSubmitAddEqGroup" class="btn btn-success" data-loading-text="Saving...">Add Group</button>
							<button type="reset" id="btnCancelAddEqGroup" class="btn btn-link btn-cancel">Cancel</button>
						</div>
					</div>
				</div>
			</form>
		<?php
		}
		else {
			# get groups for this ordinary user
			$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);
			if (count($UserEqGroups) > 0) {
				foreach ($UserEqGroups as $ueg) {
					echo $ueg->toListItemLinked();
				}
			}
			else {
				echo "<li>You do not have access to any equipment groups.</li>";
			}
			echo "</ul>";
		}
	}
	else {
		// SECTION: not yet authenticated, wants to log in
		?>
		<div class="hero-unit">
			<h2><?php echo LANG_INSTITUTION_NAME; ?></h2>

			<h1><?php echo LANG_APP_NAME; ?></h1>

			<br />

			<p>This is our system for scheduling equipment reservations.</p>

			<p>To sign in, please use your <?php echo LANG_INSTITUTION_NAME; ?> username and password.</p>

		</div>
	<?php
	}

	require_once('foot.php');
?>