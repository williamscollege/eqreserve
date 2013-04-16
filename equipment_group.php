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

			// Toggle Manager View (text or editable form)
			$("#toggleManagerOptions").click(function () {
				// toggle form or plain-text
				$("#managerView, #managerEdit, .editing-control").toggleClass("hide");
				// toggle button label
				if ($("#managerView").hasClass('hide')) {
					$("#toggleManagerOptions").html('<i class="icon-white icon-pencil"></i> Manager: View Mode');
				}
				else {
					$("#toggleManagerOptions").html('<i class="icon-white icon-pencil"></i> Manager: Edit Mode');
				}
			});

			// Toggle Reserve Equipment View (show/hide form)
			$("#toggleReserveEquipment").click(function () {
				// toggle form or plain-text
				$("#reservationShow, #reservationHide").toggleClass("hide");
				// toggle button label
				if ($("#reservationShow").hasClass('hide')) {
					$("#toggleReserveEquipment").html('<i class="icon-white icon-folder-open"></i> Reserve Equipment: Show Form');
				}
				else {
					$("#toggleReserveEquipment").html('<i class="icon-white icon-folder-open"></i> Reserve Equipment: Hide Form');
				}
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
				$("#managerView, #managerEdit .editing-control").toggleClass("hide");
			});

			// Remove later: debugging jquery validator plugin
			$("a.check").click(function () {
				alert("Valid: " + $("#formEditEqGroup").valid());
				return false;
			});

//			// if the form is altered, make sure the submit button is reset (and not in a disabled state)
			$("#formEditEqGroup INPUT, TEXTAREA, SELECT").change(function () {
				$("#btnSubmitEditEqGroup").button('reset');
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
								$("#btnSubmitEditEqGroup").text('Saved!');
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
		?>
		<a href="#" id="toggleManagerOptions" class="btn btn-medium btn-primary pull-right"><i class="icon-white icon-pencil"></i> Manager: Edit Mode</a>
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
									return "<button type=\"button\" class=\"btn btn-inverse\" title=\"$txt\" data-for-type=\"$for_type\" data-for-id=\"$id\">$txt <i class=\"icon-remove icon-white\"></i></button>";
								}, $managers)
							);
							?>

							<button type="button" class="btn btn-success" title="Add Manager"><i class="icon-plus-sign icon-white"></i> Add Manager
								<i class="icon-plus-sign icon-white"></i></button>

							<?php
							//                            echo $managersList;
							?>
							<!--<input type="text" id="groupManagers" class="input-large" disabled="disabled" name="groupManagers" value="<?php /*echo $managersList; */?>" placeholder="Managed by" maxlength="200" />-->
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
							<button type="button" class="btn btn-danger" title="Remove Selected"><i class="icon-minus-sign icon-white"></i> Remove Selected
								<i class="icon-minus-sign icon-white"></i></button>
							<button type="button" class="btn btn-success" title="Add User"><i class="icon-plus-sign icon-white"></i> Add User
								<i class="icon-plus-sign icon-white"></i></button>
						</div>
					</div>

					<legend>Reservation Rules</legend>

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
	echo "<h3>Equipment Group: " . $Requested_EqGroup->name . "</h3>\n";
	echo "<p>Description: " . $Requested_EqGroup->descr . "</p>\n";
	//	echo "<p>Managed by: " . $managersList . "</p>\n";
	echo "<p>Managed by: ";
	echo join(', ',
		array_map(function ($m) {
				if (get_class($m) == 'User') {
					return "$m->fname $m->lname";
				}
				return "[$m->name]";
			},
			$managers)
	);
	echo "</p>\n";
	echo "<h5>Reservation Rules</h5>\n";
	echo "Start times <span class=\"label label-inverse\" title=\"Reservations must start and end on one of these minutes of the hour\"> " . $Requested_EqGroup->start_minute . " minutes</span><br />\n";
	echo "Min duration <span class=\"label label-inverse\" title=\"The minimum length of time that can be reserved\">" . util_minutesToWords($Requested_EqGroup->min_duration_minutes) . " </span><br />\n";
	echo "Max duration <span class=\"label label-inverse\" title=\"The maximum length of time that can be reserved\">" . util_minutesToWords($Requested_EqGroup->max_duration_minutes) . " </span><br />\n";
	echo "Duration unit <span class=\"label label-inverse\" title=\"The time reserved must be an even multiple of this - this is the smallest about by which a reservation duration may be altered\">" . util_minutesToWords($Requested_EqGroup->duration_chunk_minutes) . " </span><br />\n";
	echo "</div>";
	?>

<br />

	<h3>Reserve Equipment</h3>
	<a href="#" id="toggleReserveEquipment" class="btn btn-medium btn-primary pull-left"><i class="icon-white icon-folder-open"></i> Reserve Equipment: Show
		Form</a><p><br /><br /></p>
	<div id="reservationShow" class="hide">
		<form action="reservation.php" id="formReservation" class="form-horizontal" name="formReservation" method="post">
			<input type="hidden" id="reservationGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />

			<div id="reservationGroupFields">
				<div class="control-group">
					<label class="control-label" for="reservationStartDate">Start Date</label>

					<div class="controls">
						<div class="input-append">
							<input type="text" id="reservationStartDate" class="input-small" maxlength="10" />
							<span id="iconHackForceStartDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
						</div>
						&nbsp;&nbsp;Time
						<!-- REMOVE LATER: http://jdewit.github.io/bootstrap-timepicker/ -->
						<div class="input-append bootstrap-timepicker">
							<input id="reservationStartTime" type="text" class="input-small" value="<?php #echo $Requested_EqGroup->start_time; ?>" maxlength="8" />
							<span class="add-on"><i class="icon-time"></i></span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="reservationEndDate">End Date</label>

					<div class="controls">
						<div class="input-append">
							<input type="text" id="reservationEndDate" class="input-small" maxlength="10" />
							<span id="iconHackForceEndDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
						</div>
						&nbsp;&nbsp;Time
						<div class="input-append bootstrap-timepicker">
							<input id="reservationEndTime" type="text" class="input-small" value="<?php #echo $Requested_EqGroup->end_time; ?>" maxlength="8" />
							<span class="add-on"><i class="icon-time"></i></span>
						</div>
					</div>
				</div>

                <script>
                    $(function () {
                        // calendar
                        $("#reservationStartDate").datepicker();
                        $("#reservationEndDate").datepicker();

                        // calendar: hack to make icon trigger
                        $("#iconHackForceStartDate").click(function () {
                            $("#reservationStartDate").datepicker('show');
                        });
                        $("#iconHackForceEndDate").click(function () {
                            $("#reservationEndDate").datepicker('show');
                        });

                        // timepicker
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
                    });
                </script>

                <?php
                # Load EQSubgroups
                $Requested_EqGroup->loadEqSubgroups();
                //			util_prePrintR($Requested_EqGroup);

                $jsPopovers = "";
                foreach ($Requested_EqGroup->eq_subgroups as $key) {
                    # Subgroups
                    echo "<h4><a href=\"#\" id=\"subGroup" . $key->eq_subgroup_id . "\" data-content=\"" . $key->descr . "\" title=\"Description\" >" . $key->name . "</a>";
                    if ($USER->flag_is_system_admin || $is_group_manager) {
                        echo " <button type=\"button\" class=\"btn btn-primary editing-control hide\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</button>";
                    }
                    echo "</h4>\n";
                    # Create javascript string for: subgroups
                    $jsPopovers .= "$('#subGroup" . $key->eq_subgroup_id . "').popover({placement: 'top', trigger: 'hover'});";

                    # Items
                    $key->loadEqItems();
                    if(count($key->eq_items) == 0){
                        echo "<div class=\"offset1\"><p>No items are associated with this subgroup.</p></div>";
                    } else {
                        foreach ($key->eq_items as $item) {
                            ?>
                            <div class="control-group">
                                <label class="control-label span1" for="item<?php echo $item->eq_item_id; ?>"><input type="checkbox" id="" />
                                    <a href="#" id="item<?php echo $item->eq_item_id; ?>" data-content="<?php echo $item->descr; ?>" title="Description"> <?php echo $item->name; ?></a></label>

                                <div class="controls">
                                    <div class="progress span8">
                                        <div class="bar bar-info" style="width: 35%;"></div>
                                        <div class="bar bar-warning" style="width: 20%;"></div>
                                        <div class="bar bar-success" style="width: 35%;"></div>
                                        <div class="bar bar-danger" style="width: 10%;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            # Create javascript string for: items
                            $jsPopovers .= "$('#item" . $item->eq_item_id . "').popover({placement: 'top', trigger: 'hover'});";
                        }
                    }
    //				# Button: Add an Item
    //				if ($USER->flag_is_system_admin || $is_group_manager) {
    //					echo "<div class=\"offset1\"><button type=\"button\" class=\"btn btn-primary\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</button></div><br />";
    //				}
                }

                if ($USER->flag_is_system_admin || $is_group_manager) {
                    echo "<br /><button type=\"button\" class=\"btn btn-primary\" title=\"Add a subgroup\"><i class='icon-plus icon-white'></i> Add a Subgroup</button>";
                }

                ?>

				<script type="text/javascript">
					$(document).ready(function () {
						// ***************************
						// Popover Listeners
						// ***************************
						$('#subGroup301').popover({placement: 'top', trigger: 'hover'});
						$('#item403').popover({placement: 'top', trigger: 'hover'});
						<?php
							echo $jsPopovers;
						?>
					});
				</script>

			</div>
		</form>
	</div>
	<?php
	require_once('foot.php');
}
?>