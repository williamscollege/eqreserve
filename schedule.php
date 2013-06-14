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
		var headToOnScheduleGone = '<?php echo (isset($_REQUEST["returnToEqGroup"]))?('equipment_group.php?eid='.$SCHED->reservations[0]->eq_item->eq_group->eq_group_id):'account_management.php'; ?>';
		var scheduleId = <?php echo $SCHED->schedule_id; ?>;
	</script>
	<script type="text/javascript" src="js/schedule.js"></script>

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