<?php
	$pageTitle = 'Schedule and Reservations';
	require_once('head.php');

	$SCHED = Schedule::getOneFromDb(['schedule_id' => $_REQUEST['schedule']], $DB);
	if ((!$USER->flag_is_system_admin) && ($SCHED->user_id != $USER->user_id)) {
		util_redirectToAppHome('failure', 52);
	}

	$SCHED->loadReservationsDeeply();
?>
	<script type="text/javascript">
	var ajax_url = 'ajax_schedule.php';
	var headToOnScheduleGone = '<?php echo (isset($_REQUEST["returnToEqGroup"]))?('equipment_group.php?eid='.$SCHED->reservations[0]->eq_item->eq_group->eq_group_id):'account_management.php'; ?>';

	$(document).ready(function () {
		// Toggle view vs edit
		$("#toggleEditMode").click(function () {
			// toggle form or plain-text
			$(".editing-control").toggleClass("hide");
			$(".view-control").toggleClass("hide");

			// toggle button label
			if ($("#toggleEditMode").attr('data-cur-mode') == 'view') {
				$("#toggleEditMode").html('<i class="icon-white icon-ok"></i> View');
				$("#toggleEditMode").attr('data-cur-mode', 'edit');
			}
			else {
				$("#toggleEditMode").html('<i class="icon-white icon-pencil"></i> Edit');
				$("#toggleEditMode").attr('data-cur-mode', 'view');
			}
		});

		$("#sched-notes").blur(function () {
			// check for change from original/previous notes, do the update if it differs
			if ($('#sched-notes-view').html() != $("#sched-notes").val()) {
				eqrUtil_setTransientAlert('progress', 'saving...');
				$.ajax({
					url: ajax_url,
					dataType: 'json',
					data: {'schedule':<?php echo $SCHED->schedule_id; ?>,
						'scheduleAction': 'updateNotes',
						'actionVal': $("#sched-notes").val()
					}
				})
					.done(function (data, status, xhr) {
						if (data.status == 'success') {
							eqrUtil_setTransientAlert('success', 'saved');
							$('#sched-notes-view').html($("#sched-notes").val());
						}
						else {
							eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
						}
					})
					.fail(function (data, status, xhr) {
						eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
					})
					//                    .always(function(d,s,x){
					//                        for (p in d) {
					//                            if (d.hasOwnProperty(p)) {
					//                                console.log(p+': '+d[p]);
					//                            }
					//                        }
					//                    })
				;
			}
		});

		$("#sched-is-manager-btn").click(function () {
			var curType = $("#resv-current-type").html();
			var schedNewType = (curType == 'management') ? 'consumer' : 'manager';
			eqrUtil_setTransientAlert('progress', 'saving...');
			$.ajax({
				url: ajax_url,
				dataType: 'json',
				data: {'schedule':<?php echo $SCHED->schedule_id; ?>,
					'scheduleAction': 'updateType',
					'actionVal': schedNewType
				}
			})
				.done(function (data, status, xhr) {
					if (data.status == 'success') {
						eqrUtil_setTransientAlert('success', 'saved');
						// toggle form or plain-text
						if (curType == 'management') {
							$("#resv-current-type").html('regular')
							$("#resv-other-type").html('management')
							$('#sched-is-manager-view-text').html('This is a regular user schedule');
						}
						else {
							$("#resv-current-type").html('management')
							$("#resv-other-type").html('regular')
							$('#sched-is-manager-view-text').html('This is a management schedule!');
						}
						$('#sched-is-manager-btn i.signifier').toggleClass('hide');
						$('#sched-is-manager-btn').toggleClass('btn-warning');
						$('#sched-is-manager-btn').toggleClass('btn-info');
						$('#sched-is-manager-view').toggleClass('text-warning');
						$('#sched-is-manager-view').toggleClass('text-info');
						$('#sched-is-manager-view i').toggleClass('icon-user');
						$('#sched-is-manager-view i').toggleClass('icon-wrench');
					}
					else {
						eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
					}
				})
				.fail(function (data, status, xhr) {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				})
			;

		});

		$(".delete-time-block-btn").click(function () {
			GLOBAL_confirmHandlerData = $(this).attr('data-for-time-block');
			var confirmText = "<p>Are you sure you want to remove that time block from this schedule of reservations?</p>\n";
			if ($("#time_blocks li").length == 1) {
				confirmText += "<p><b>NOTE:</b> since that is the only time block removing it will remove this entire schedule of reservations!</p>";
			}
			eqrUtil_launchConfirm(confirmText, handleDeleteTimeBlock);
		});
		function handleDeleteTimeBlock() {
			eqrUtil_setTransientAlert('progress', 'saving...');
			$.ajax({
				url: ajax_url,
				dataType: 'json',
				data: {'schedule':<?php echo $SCHED->schedule_id; ?>,
					'scheduleAction': 'deleteTimeBlock',
					'actionVal': GLOBAL_confirmHandlerData
				}
			})
				.done(function (data, status, xhr) {
					if (data.status == 'success') {
						eqrUtil_setTransientAlert('success', 'saved');
						// if there's only one item in the list, jump to user acct page
						// else remove the relevant list item
						if ($('ul#time_blocks > li').length <= 1) {
							eqrUtil_setTransientAlert('success', 'schedule deleted! Redirecting...');
							window.location.href = headToOnScheduleGone;
						}
						else {
							$('#list-of-time-block-' + GLOBAL_confirmHandlerData).remove();
						}
					}
					else {
						eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
					}
				})
				.fail(function (data, status, xhr) {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				})
			;
		}

		$(".delete-reservation-btn").click(function () {
			GLOBAL_confirmHandlerData = $(this).attr('data-for-reservation');
			var confirmText = "<p>Are you sure you want to remove that item from this schedule of reservations?</p>\n";
			if ($("#reservations li").length == 1) {
				confirmText += "<p><b>NOTE:</b> since that is the only item removing it will remove this entire schedule of reservations!</p>";
			}
			eqrUtil_launchConfirm(confirmText, handleDeleteReservation);
		});
		function handleDeleteReservation() {
			eqrUtil_setTransientAlert('progress', 'saving...');
			$.ajax({
				url: ajax_url,
				dataType: 'json',
				data: {'schedule':<?php echo $SCHED->schedule_id; ?>,
					'scheduleAction': 'deleteReservation',
					'actionVal': GLOBAL_confirmHandlerData
				}
			})
				.done(function (data, status, xhr) {
					if (data.status == 'success') {
						eqrUtil_setTransientAlert('success', 'saved');
						// if there's only one item in the list, jump to user acct page
						// else remove the relevant list item
						if ($('ul#reservations > li').length <= 1) {
							eqrUtil_setTransientAlert('success', 'schedule deleted! Redirecting...');
							window.location.href = headToOnScheduleGone;
						}
						else {
							$('#list-of-reservation-' + GLOBAL_confirmHandlerData).remove();
						}
					}
					else {
						eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
					}
				})
				.fail(function (data, status, xhr) {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				})
			;
		}

		$("#deleteEntireScheduleBtn").click(function () {
			GLOBAL_confirmHandlerData = $(this).attr('data-for-schedule');
			eqrUtil_launchConfirm("<p>Are you sure you want to delete this entire schedule of reservations?</p>\n", handleDeleteSchedule);
		});
		function handleDeleteSchedule() {
			eqrUtil_setTransientAlert('progress', 'saving...');
			$.ajax({
				url: ajax_url,
				dataType: 'json',
				data: {'schedule':<?php echo $SCHED->schedule_id; ?>,
					'scheduleAction': 'deleteSchedule',
					'actionVal': GLOBAL_confirmHandlerData
				}
			})
				.done(function (data, status, xhr) {
					if (data.status == 'success') {
//                            eqrUtil_setTransientAlert('success','saved');
						eqrUtil_setTransientAlert('success', 'schedule deleted! Redirecting...');
						window.location.href = headToOnScheduleGone;
					}
					else {
						eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
					}
				})
				.fail(function (data, status, xhr) {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				})
			;
		}

	});
	</script>

	<!--
	<div id="page_alert" class="transient_alert in_progress_alert hide alert">
		Saved
	</div>
	-->
	<legend class="pull-left row-fluid">Schedule of Reservations
		<a href="#" id="toggleEditMode" class="btn btn-medium btn-primary" data-cur-mode="view"><i class="icon-white icon-pencil"></i> Edit</a></legend>

	<div class="control-group">
		<label class="control-label" for="reservations">Reservations on
			<ul id="time_blocks" class="unstyled">
				<?php
					$SCHED->loadTimeBlocks();
					foreach ($SCHED->time_blocks as $tb) {
						echo '<li id="list-of-time-block-' . $tb->time_block_id . '">';
						echo '<a href="#" id="delete-time-block-' . $tb->time_block_id . '" class="editing-control hide btn btn-mini btn-danger btn-delete-list-item delete-time-block-btn" data-for-time-block="' . $tb->time_block_id . '"><i class="icon-trash icon-white"></i> </a> ';
						echo $tb->toString() . "</li>\n";
					}
				?>
			</ul>
		</label>

		<p>
			<small class="view-control"><span id="sched-notes-view"><?php echo $SCHED->notes; ?></span></small>
		<div class="editing-control hide">
			<textarea id="sched-notes" name="sched-notes" class="notes-editing-region"><?php echo $SCHED->notes; ?></textarea>
		</div>
		</p>

		<?php
			if ($SCHED->type == 'manager') {
				echo '<p id="sched-is-manager-view" class="view-control text-warning"><i class="icon-wrench"></i> <strong id="sched-is-manager-view-text">This is a management schedule!</strong></p>';
			}
			else {
				echo '<p id="sched-is-manager-view" class="view-control text-info"><i class="icon-user"></i> <strong id="sched-is-manager-view-text">This is a regular user schedule</strong></p>';
			}
			if ($USER->canManageEqGroup($SCHED->reservations[0]->eq_item->eq_group->eq_group_id)) {
				?>
				<div class="editing-control hide text-warning">
					<a href="#" id="sched-is-manager-btn" class="btn btn-medium <?php echo ($SCHED->type == 'manager') ? 'btn-warning' : 'btn-info'; ?>">
						<i class="icon-wrench signifier<?php echo ($SCHED->type == 'manager') ? '' : ' hide'; ?>"></i>
						<i class="icon-user signifier<?php echo ($SCHED->type == 'manager') ? ' hide' : ''; ?>"></i>
						This is a <strong><span id="resv-current-type"><?php echo ($SCHED->type == 'manager') ? 'management' : 'regular'; ?></span></strong>
						schedule;
						make it a <strong><span id="resv-other-type"><?php echo ($SCHED->type == 'manager') ? 'regular' : 'management'; ?></span></strong>
						schedule instead</span></a>
					<br /><br />
				</div>
			<?php
			}
		?>
		<div class="controls">
			For
			<a href="equipment_group.php?eid=<?php echo $SCHED->reservations[0]->eq_item->eq_group->eq_group_id; ?>"><?php echo $SCHED->reservations[0]->eq_item->eq_group->name; ?></a>
			you have reserved:

			<ul id="reservations">
				<?php
					foreach ($SCHED->reservations as $r) {
						echo '<li id="list-of-reservation-' . $r->reservation_id . '">';
						echo '<a href="#" id="delete-reservation-' . $r->reservation_id . '" class="editing-control hide btn btn-mini btn-danger btn-delete-list-item delete-reservation-btn" data-for-reservation="' . $r->reservation_id . '"><i class="icon-trash icon-white"></i> </a> ';
						echo $r->eq_item->eq_subgroup->name . ': ' . $r->toString() . "</li>\n";
					}
				?>
			</ul>
		</div>
	</div>

	<br /><br />
	<a href="#" id="deleteEntireScheduleBtn" class="editing-control hide btn btn-medium btn-danger" data-for-schedule="<?php echo $SCHED->schedule_id; ?>"><i class="icon-trash icon-white"></i>
		DELETE ENTIRE SCHEDULE AND RESERVATIONS</a>

<?php
	require_once('foot.php');
?>