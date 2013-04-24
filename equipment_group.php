<?php
	$pageTitle = 'Edit Equipment Group';
	require_once('head.php');
	require_once('/classes/eq_group.class.php');


	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated

		// fetch querystring
		if (!array_key_exists('eid', $_REQUEST)) {
			util_redirectToAppHome('failure', 20);
		}
		$eid = intval($_REQUEST["eid"]);

		// declare variables
		$Requested_EqGroup = [];
		$is_group_access   = FALSE;
		$is_group_manager  = FALSE;

		if ($USER->flag_is_system_admin) {
			$Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => $eid], $DB);
			# security: ensure querystring is valid and user has access to that record
			if ($Requested_EqGroup->matchesDb) {
				$is_group_access  = TRUE;
				$is_group_manager = TRUE;
				//			echo "<pre>Requested_EqGroup:"; print_r($Requested_EqGroup); echo "</pre>";
			}
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

		# get list of all managers for this group
		$Requested_EqGroup->loadPermissions();

		$managers  = [];
		$consumers = [];
		foreach ($Requested_EqGroup->permissions as $perm) {
			if ($perm->role_id == 1) {
				if ($perm->entity_type == 'user') {
					array_push($managers, User::getOneFromDb(['user_id' => $perm->entity_id], $DB));
				}
				elseif ($perm->entity_type == 'inst_group') {
					array_push($managers, InstGroup::getOneFromDb(['inst_group_id' => $perm->entity_id], $DB));
				}
			}
			else {
				if ($perm->entity_type == 'user') {
					array_push($consumers, User::getOneFromDb(['user_id' => $perm->entity_id], $DB));
				}
				elseif ($perm->entity_type == 'inst_group') {
					array_push($consumers, InstGroup::getOneFromDb(['inst_group_id' => $perm->entity_id], $DB));
				}
			}
		}

		?>
		<script type="text/javascript">
		$(document).ready(function () {
			// default conditions

			// ***************************
			// Listeners
			// ***************************

			// Toggle Equipment Group Settings (text or input fields)
			$("#toggleGroupSettings").click(function () {
				// toggle form or plain-text
				$("#managerView, #managerEdit").toggleClass("hide");
				// toggle button label
				if ($("#managerView").hasClass('hide')) {
					$("#toggleGroupSettings").html('<i class="icon-white icon-ok"></i> View');
					// hide the other form
					$("#btnCancelReservation").click();
					// show special manager actions
					$(".manager-action").removeClass("hide");
				}
				else {
					$("#toggleGroupSettings").html('<i class="icon-white icon-pencil"></i> Edit');
					// hide special manager actions
					$(".manager-action").addClass("hide");
				}
			});

			// Toggle Reserve Equipment (show or hide form fields)
			$("#toggleReserveEquipment").click(function () {
				// toggle form or plain-text
				$(".reservationForm").toggleClass("hide");
				// toggle button label
				if ($(".reservationForm").hasClass('hide')) {
					$("#toggleReserveEquipment").html('<i class="icon-white icon-pencil"></i> Reserve Equipment');
				}
				else {
					$("#toggleReserveEquipment").html('<i class="icon-white icon-ok"></i> View Equipment');
					// hide the other form
					$("#btnCancelEditEqGroup").click();
					// hide special manager actions
					$(".manager-action").addClass("hide");
				}
			});

			// Cancel and cleanup
			$("#btnCancelEditEqGroup").click(function () {
				cleanUpForm("formEditEqGroup")
				// hide form fields
				$("#managerEdit").addClass("hide");
				$("#managerView").removeClass("hide");
				$("#toggleGroupSettings").html('<i class="icon-white icon-pencil"></i> Edit');
				// hide special manager actions
				$(".manager-action").addClass("hide");
			});
			// Cancel and cleanup
			$("#btnCancelReservation").click(function () {
				cleanUpForm("formReservation")
				// hide form fields, restore button label
				$(".reservationForm").addClass("hide");
				$("#toggleReserveEquipment").html('<i class="icon-white icon-pencil"></i> Reserve Equipment');
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

			// Reserve Equipment: calendar
			$("#reservationStartDate, #reservationEndDate").datepicker({
				dateFormat: 'M dd, yy'
			}).val($.datepicker.formatDate('M dd, yy', new Date()));
			// Reserve Equipment: calendar: hack to make icon trigger
			$("#iconHackForceStartDate").click(function () {
				$("#reservationStartDate").datepicker('show');
			});
			$("#iconHackForceEndDate").click(function () {
				$("#reservationEndDate").datepicker('show');
			});
			// Reserve Equipment: timepicker
			$("#reservationStartTime").timepicker({
				minuteStep: 15,
				defaultTime: 'current', /* or set to a specific time: '11:45 AM' */
				showMeridian: true  /* true is 12hr mode, false is 12hr mode */
			});
			$("#reservationEndTime").timepicker({
				minuteStep: 15,
				defaultTime: 'current', /* or set to a specific time: '11:45 AM' */
				showMeridian: true  /* true is 12hr mode, false is 12hr mode */
			});

			// Remove later: debugging jquery validator plugin
			$("a.check").click(function () {
				alert("is 'formEditEqGroup' Valid?: " + $("#formEditEqGroup").valid() + "\n" + "is 'formReservation' Valid?: " + $("#formReservation").valid());
				return false;
			});


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
						min: 1,
						required: true
					},
					maxDurationMinutes: {
						digits: true,
						min: 1,
						required: true
					},
					durationIntervalMinutes: {
						digits: true,
						min: 1,
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
					// show loading text (button)
					$("#btnSubmitEditEqGroup").button('loading');

					// Update printed text values on screen with submitted values
					$("#print_groupName").text($("#groupName").val());
					$("#print_groupDescription").text($("#groupDescription").val());
					$("#print_startMinute").text($("#startMinute").val());
					$("#print_minDurationMinutes").text($("#minDurationMinutes").val());
					$("#print_maxDurationMinutes").text($("#maxDurationMinutes").val());
					$("#print_durationIntervalMinutes").text($("#durationIntervalMinutes").val());

					var url = $("#formEditEqGroup").attr('action');
					var formName = "formEditEqGroup";
//							alert('url=' + url + '\n' + 'formName=' + formName + '\n');

					$.ajax({
						type: 'POST',
						url: url,
						data: {
							ajaxVal_ID: $('#eqGroupID').val(),
							ajaxVal_Name: $('#groupName').val(),
							ajaxVal_Description: $('#groupDescription').val(),
							ajaxVal_StartMinute: $('#startMinute').val(),
							ajaxVal_MinDurationMinute: $('#minDurationMinutes').val(),
							ajaxVal_MaxDurationMinute: $('#maxDurationMinutes').val(),
							ajaxVal_DurationIntervalMinutes: $('#durationIntervalMinutes').val()
						},
						dataType: 'html',
						success: function (data) {
							// reset the submit button (avoid disabled state)
							$("#btnSubmitEditEqGroup").button('reset');

							if (data) {
								// hide
								$("#btnCancelEditEqGroup").click();
							}
							else {
								// reset form
								cleanUpForm("formEditEqGroup")
								// show error
								$("#btnSubmitEditEqGroup").append('<p><span class="label label-important">Important</span> An error occurred!</p>');
							}
						}
					});

				}
			})


			var validator2 = $('#frmAjaxAddSubgroup').validate({
				rules: {
					ajaxSubgroupName: {
						minlength: 2,
						required: true
					},
					ajaxSubgroupDescription: {
						minlength: 2,
						required: true
					},
					ajaxSubgroupIsMultiSelect: {
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
					$("#btnAjaxSubmitAddSubgroup").button('loading');

					var url = $("#frmAjaxAddSubgroup").attr('action');			// get url from the form element
					var formName = $("#frmAjaxAddSubgroup").attr('name');		// get name from the form element
					var data1 = $('#' + formName + ' #ajaxEqGroupID').val();
					var data2 = $('#' + formName + ' #ajaxSubgroupName').val();
					var data3 = $('#' + formName + ' #ajaxSubgroupDescription').val();
					var data4 = $('#' + formName + ' #ajaxSubgroupIsMultiSelect').val();
					// alert('url=' + url + '\n' + 'formName=' + formName + '\n' + 'data1=' + data1 + '\n' + 'data2=' + data2);

					$.ajax({
						type: 'POST',
						url: url,
						data: {
							ajaxVal_ID: data1,
							ajaxVal_Name: data2,
							ajaxVal_Description: data3,
							ajaxVal_MultiSelect: data4
						},
						dataType: 'html',
						success: function (data) {
							// reset form
							cleanUpForm("frmAjaxAddSubgroup")

							if (data) {
								// update the element with new data from the ajax call
								$("UL#displaySubGroups").append(data);
								// hide
								$("#btnAjaxCancelAddSubgroup").click();
							}
							else {
								// show error
								$("UL#displaySubGroups").append('<li><span class="label label-important">Important</span> An error occurred!</li>');
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
				validator.resetForm(this);
				// manually remove input highlights
				$(".control-group").removeClass('success').removeClass('error');
			}


			// Modals
			$('.ajaxAction').click(function () {
				// pass id value into modal
				var par = $(this).attr("data-subgroupid");
				var lbl = $(this).attr("data-subgroupname");
				$("INPUT#ajaxSubgroupID").val(par);
				$("H3#modalAddItemLabel").text(lbl);
			});
			$('#btnAjaxCancelAddItem, #btnAjaxCancelAddSubgroup').click(function () {
				// clear form: reset
				$("input[type=radio], input[type=hidden], input[type=text], textarea").val("");
			});

		});
		</script>

		Remove this later: <a href="#" class="check">is form valid?</a><br>
		<?php

		# admin or manager: is allowed to edit fields
		if ($USER->flag_is_system_admin || $is_group_manager) {
			?>
			<a href="#" id="toggleGroupSettings" class="btn btn-medium btn-primary pull-right"><i class="icon-white icon-pencil"></i> Edit</a>
			<div id="managerEdit" class="hide">
				<form action="ajax_edit_eq_group.php" class="form-horizontal" id="formEditEqGroup" name="formEditEqGroup" method="post">
					<input type="hidden" id="eqGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />

					<h3>Equipment Group</h3>

					<div id="eqGroupFields">
						<div class="control-group">
							<label class="control-label" for="groupName">Group</label>

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

								<?php
									echo join(" ",
										array_map(function ($m) {
											$txt      = '';
											$id       = 0;
											$for_type = get_class($m);
											if (get_class($m) == 'User') {
												$id  = $m->user_id;
												$txt = "$m->fname $m->lname ($m->email)";
											}
											else {
												$id  = $m->inst_group_id;
												$txt = "[$m->name]";
											}
											return "<button type=\"button\" class=\"btn btn-inverse btn-small\" title=\"$txt\" data-for-type=\"$for_type\" data-for-id=\"$id\">$txt <i class=\"icon-remove icon-white\"></i></button>";
										}, $managers)
									);
								?>

								<button type="button" class="btn btn-success btn-small" title="Add Manager"><i class="icon-plus-sign icon-white"></i> Add
									Manager
									<i class="icon-plus-sign icon-white"></i></button>

								<?php
								?>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="groupManagers">Reservable by</label>

							<div class="controls">
								<i>use CTRL and/or SHIFT to select more than one</i></i><br />
								<select name="consumers-select" id="consumers-select" class="user-select" size="12" multiple="multiple">
									<?php
										echo join(" ",
											array_map(function ($c) {
												$txt      = '';
												$id       = 0;
												$for_type = get_class($c);
												if (get_class($c) == 'User') {
													$id  = $c->user_id;
													$txt = "$c->fname $c->lname ($c->email)";
												}
												else {
													$id  = $c->inst_group_id;
													$txt = "[$c->name]";
												}
												return "<option title=\"$txt\" data-for-type=\"$for_type\" data-for-id=\"$id\">$txt</option>";
											}, $consumers)
										);
									?>
								</select><br /><br />
								<button type="button" class="btn btn-danger btn-small" title="Remove Selected"><i class="icon-minus-sign icon-white"></i> Remove
									Selected
									<i class="icon-minus-sign icon-white"></i></button>
								<button type="button" class="btn btn-success btn-small" title="Add User"><i class="icon-plus-sign icon-white"></i> Add User
									<i class="icon-plus-sign icon-white"></i></button>
							</div>
						</div>

						<h3>Reservation Rules</h3>

						<div class="control-group">
							<label class="control-label" for="goStartMinute">Start time (minutes)</label>

							<div class="controls">
								<?php
									$defaultStartMinute = [
										""           => "Select or Edit",
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
								<input type="text" id="startMinute" class="input-medium" name="startMinute" value="<?php echo $Requested_EqGroup->start_minute; ?>" placeholder="Minutes (with commas)" maxlength="200" />
								Reservations must start and end on one of these minutes of the hour.
							</div>
						</div>
						<?php
							$defaultDuration = [
								""    => "Select or Edit",
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
								<button type="submit" id="btnSubmitEditEqGroup" class="btn btn-success" data-loading-text="Saving...">Save</button>
								<button type="button" id="btnCancelEditEqGroup" class="btn btn-link btn-cancel">Cancel</button>
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
		echo "<h3>Equipment Group</h3>\n";
		echo "Group: <span id=\"print_groupName\">" . $Requested_EqGroup->name . "</span><br />\n";
		echo "Description: <span id=\"print_groupDescription\">" . $Requested_EqGroup->descr . "</span><br />\n";
		echo "Managed by: ";
		echo join(', ',
			array_map(function ($m) {
					if (get_class($m) == 'User') {
						return "$m->fname $m->lname";
					}
					return "[$m->name]";
				},
				$managers)
		);
		echo "<br />\n";
		echo "<h3>Reservation Rules</h3>\n";
		echo "Start times <span class=\"label label-inverse\" title=\"Reservations must start and end on one of these minutes of the hour\"><span id=\"print_startMinute\">" . $Requested_EqGroup->start_minute . "</span> minutes</span><br />\n";
		echo "Min duration <span class=\"label label-inverse\" title=\"The minimum length of time that can be reserved\"><span id=\"print_minDurationMinutes\">" . util_minutesToWords($Requested_EqGroup->min_duration_minutes) . "</span></span><br />\n";
		echo "Max duration <span class=\"label label-inverse\" title=\"The maximum length of time that can be reserved\"><span id=\"print_maxDurationMinutes\">" . util_minutesToWords($Requested_EqGroup->max_duration_minutes) . "</span></span><br />\n";
		echo "Duration unit <span class=\"label label-inverse\" title=\"The time reserved must be an even multiple of this - this is the smallest about by which a reservation duration may be altered\"><span id=\"print_durationIntervalMinutes\">" . util_minutesToWords($Requested_EqGroup->duration_chunk_minutes) . "</span></span><br />\n";
		echo "</div>";
		?>

		<br />

		<a href="#" id="toggleReserveEquipment" class="btn btn-medium btn-primary pull-right"><i class="icon-white icon-pencil"></i> Reserve
			Equipment</a>
		<form action="reservation.php" class="form-horizontal" id="formReservation" name="formReservation" method="post">
			<input type="hidden" id="reservationGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />

			<h3>Reserve Equipment</h3>

			<div id="reservationGroupFields">

				<?php
					# Load EQSubgroups
					$Requested_EqGroup->loadEqSubgroups();
					//			util_prePrintR($Requested_EqGroup);

					echo "<ul id=\"displaySubGroups\" class=\"unstyled\">";
					foreach ($Requested_EqGroup->eq_subgroups as $key) {

						# Subgroup Items
						$key->loadEqItems();

						if (count($key->eq_items) == 0) {
							if ($USER->flag_is_system_admin || $is_group_manager) {
								# Subgroup Title
								echo "<strong>" . $key->name . ":</strong> " . $key->descr . "\n";
								echo "<li><div class=\"span1\">&nbsp;</div><em>No items exist.</em></li>";
								# Button: Add an Item
								echo "<li class=\"manager-action hide\">";
								echo "<div class=\"span1\"></div>";
								echo "<a href=\"#modalAddItem\" data-subgroupid=\"" . $key->eq_subgroup_id . "\" data-subgroupname=\"" . $key->name . "\" data-toggle=\"modal\" class=\"btn btn-primary btn-mini ajaxAction\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
								echo "</li>";
							}
						}
						else {
							# Subgroup Title
							echo "<strong>" . $key->name . ":</strong> " . $key->descr . "\n";
							foreach ($key->eq_items as $item) {
								?>
								<li>
									<div class="span1">&nbsp;</div>
									<?php
										if ($key->flag_is_multi_select == 0) {
											# radio: single select
											?>
											<label class="" for="item<?php echo $item->eq_item_id; ?>">
												<input type="radio" id="item<?php echo $item->eq_item_id; ?>" name="subgroup<?php echo $key->eq_subgroup_id; ?>" class="reservationForm hide" />

												<strong><?php echo $item->name; ?></strong>: <?php echo $item->descr; ?>
											</label>
										<?php
										}
										elseif ($key->flag_is_multi_select == 1) {
											# checkbox: multiple select
											?>
											<label class="" for="item<?php echo $item->eq_item_id; ?>">
												<input type="checkbox" id="item<?php echo $item->eq_item_id; ?>" class="reservationForm hide" />

												<strong><?php echo $item->name; ?></strong>: <?php echo $item->descr; ?>
											</label>
										<?php
										}
									?>
									<!--Placeholder: Save For Later Use
									<div class="controls">
										<div class="progress span8">
											<div class="bar bar-info" style="width: 35%;"></div>
											<div class="bar bar-warning" style="width: 20%;"></div>
											<div class="bar bar-success" style="width: 35%;"></div>
											<div class="bar bar-danger" style="width: 10%;"></div>
										</div>
									</div>-->
								</li>
							<?php
							} # end of foreach: eq_items

							# Button: Add an Item
							if ($USER->flag_is_system_admin || $is_group_manager) {
								echo "<li class=\"manager-action hide\">";
								echo "<div class=\"span1\"></div>";
								echo "<a href=\"#modalAddItem\" data-subgroupid=\"" . $key->eq_subgroup_id . "\" data-subgroupname=\"" . $key->name . "\" data-toggle=\"modal\" class=\"btn btn-primary btn-mini ajaxAction\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
								echo "</li>";
							}
						}
					} # end of foreach: eq_subgroups
					echo "</ul>";

					if ($USER->flag_is_system_admin || $is_group_manager) {
						//						echo "<div class=\"manager-action hide\"><br /><button type=\"button\" class=\"btn btn-primary btn-small\" title=\"Add a subgroup\"><i class='icon-plus icon-white'></i> Add a Subgroup</button></div>";
						echo "<div class=\"manager-action hide\"><br /><a href=\"#modalAddSubgroup\" data-toggle=\"modal\" class=\"btn btn-primary btn-mini\" title=\"Add a subgroup to this equipment group\"><i class='icon-plus icon-white'></i> Add a Subgroup</a></div>";
					}
				?>

				<div class="control-group reservationForm hide">
					<h4>Schedule Reservation</h4>
					<label class="control-label" for="reservationStartDate">Start Date</label>

					<div class="controls">
						<div class="input-append">
							<input type="text" id="reservationStartDate" class="input-small" maxlength="12" />
							<span id="iconHackForceStartDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
						</div>
						&nbsp;&nbsp;Time
						<!-- REMOVE LATER: http://jdewit.github.io/bootstrap-timepicker/ -->
						<div class="input-append bootstrap-timepicker">
							<input id="reservationStartTime" type="text" class="input-small" value="" maxlength="8" />
							<span class="add-on"><i class="icon-time"></i></span>
						</div>
					</div>
				</div>
				<div class="control-group reservationForm hide">
					<label class="control-label" for="reservationEndDate">End Date</label>

					<div class="controls">
						<div class="input-append">
							<input type="text" id="reservationEndDate" class="input-small" maxlength="12" />
							<span id="iconHackForceEndDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
						</div>
						&nbsp;&nbsp;Time
						<div class="input-append bootstrap-timepicker">
							<input id="reservationEndTime" type="text" class="input-small" value="" maxlength="8" />
							<span class="add-on"><i class="icon-time"></i></span>
						</div>
					</div>
				</div>


				<div class="control-group reservationForm hide">
					<label class="control-label" for="repeatReservation">Repeat Reservation?</label>

					<div class="controls">
						<input type="radio" checked="true" id="repeatReservation" name="repeatReservation" value="not_repeated"> not repeated |
						<input type="radio" id="repeatReservation" name="repeatReservation" value="daily"> daily |
						<input type="radio" id="repeatReservation" name="repeatReservation" value="weekly"> weekly |
						<input type="radio" id="repeatReservation" name="repeatReservation" value="monthly"> monthly
						<br />(repeat rates cause addition ui elements to appear to support specifying days of week, or month, and an end date)
					</div>
				</div>

				<?php
					if ($USER->flag_is_system_admin || $is_group_manager) {
						?>
						<div class="control-group reservationForm hide">
							<label class="control-label" for="managerReservation">Maintenance Period?</label>

							<div class="controls">
								<input type="checkbox" id="managerReservation" name="managerReservation"> Check box to indicate this is a maintenance or non-use
								period
							</div>
						</div>
					<?php
					}
				?>

				<div class="control-group reservationForm hide">
					<label class="control-label" for="btnSubmitReservation"></label>

					<div class="controls">
						<button type="submit" id="btnSubmitReservation" class="btn btn-success" data-loading-text="Saving...">Save</button>
						<button type="button" id="btnCancelReservation" class="btn btn-link btn-cancel">Cancel</button>
					</div>
				</div>
			</div>

		</form>
		</div>


		<!-- Modal: Item-->
		<div id="modalAddItem" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalAddItemLabel" aria-hidden="true">
			<form action="ajax_add_eq_subgroup_item.php" id="frmAjaxAddSubgroupItem" name="frmAjaxAddSubgroupItem" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
					<h3 id="modalAddItemLabel"></h3>
				</div>
				<div class="modal-body">
					<div class="control-group">
						<label class="control-label" for="ajaxItemName">Name</label>

						<div class="controls">
							<input type="hidden" id="ajaxSubgroupID" name="ajaxSubgroupID" value="" />
							<input type="text" id="ajaxItemName" name="ajaxItemName" class="input-large" value="" placeholder="Name of Item" maxlength="200" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="ajaxItemDescription">Description</label>

						<div class="controls">
							<input type="text" id="ajaxItemDescription" class="input-xlarge" name="ajaxItemDescription" value="" placeholder="Description of Item" maxlength="200" />
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="btnAjaxSubmitAddItem" name="btnAjaxSubmitAddItem" class="btn btn-success pull-left" data-loading-text="Saving...">
						Add Item
					</button>
					<button type="reset" id="btnAjaxCancelAddItem" class="btn btn-link btn-cancel pull-left" data-dismiss="modal" aria-hidden="true">Cancel
					</button>
				</div>
			</form>
		</div>

		<!-- Modal: Subgroup-->
		<div id="modalAddSubgroup" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalAddSubgroupLabel" aria-hidden="true">
			<form action="ajax_add_eq_subgroup.php" id="frmAjaxAddSubgroup" name="frmAjaxAddSubgroup" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
					<h3 id="modalAddSubgroupLabel">Add a Subgroup</h3>
				</div>
				<div class="modal-body">
					<div class="control-group">
						<label class="control-label" for="ajaxSubgroupName">Name</label>

						<div class="controls">
							<input type="hidden" id="ajaxEqGroupID" name="ajaxEqGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />
							<input type="text" id="ajaxSubgroupName" name="ajaxSubgroupName" class="input-large" value="" placeholder="Name of Subgroup" maxlength="200" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="ajaxSubgroupDescription">Description</label>

						<div class="controls">
							<input type="text" id="ajaxSubgroupDescription" class="input-xlarge" name="ajaxSubgroupDescription" value="" placeholder="Description of Subgroup" maxlength="200" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="ajaxSubgroupIsMultiSelect">Allow one or many items in this subgroup to be selected</label>

						<div class="controls">
							<input type="radio" id="ajaxSubgroupSingle" name="ajaxSubgroupIsMultiSelect" value="0" /> Single Select (radio buttons)<br />
							<input type="radio" id="ajaxSubgroupMulti" name="ajaxSubgroupIsMultiSelect" value="1" /> Multiple Select (checkboxes)
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="btnAjaxSubmitAddSubgroup" name="btnAjaxSubmitAddSubgroup" class="btn btn-success pull-left" data-loading-text="Saving...">
						Add Subgroup
					</button>
					<button type="reset" id="btnAjaxCancelAddSubgroup" class="btn btn-link btn-cancel pull-left" data-dismiss="modal" aria-hidden="true">Cancel
					</button>
				</div>
			</form>
		</div>

		<?php
		require_once('foot.php');
	}
?>