<?php
	require_once('../classes/schedule.class.php');
	require_once('../classes/reservation.class.php');
	require_once('../classes/time_block.class.php');
	require_once('../classes/queued_message.class.php');

	require_once('head_ajax.php');

	#------------------------------------------------#
	$schedule  = (isset($_REQUEST["schedule"])) ? $_REQUEST["schedule"] : 0;
	$action    = (isset($_REQUEST["scheduleAction"])) ? $_REQUEST["scheduleAction"] : 0;
	$actionVal = htmlentities((isset($_REQUEST["actionVal"])) ? $_REQUEST["actionVal"] : 0);
	#------------------------------------------------#

	function doDeleteEntireSchedule($s) {
		$alertMessageData = array('item_names' => [], 'time_ranges' => []); // containing: items and times

		$s->loadTimeBlocks();
		$s->loadReservations();
		foreach ($s->time_blocks as $tb) {
			$start_datetime = new DateTime($tb->start_datetime);
			$end_datetime   = new DateTime($tb->end_datetime);
			array_push($alertMessageData['time_ranges'], $start_datetime->format('Y-m-d H:i A') . ' to ' . $end_datetime->format('Y-m-d H:i A'));

			if (!$tb->doDelete()) {
				doRevertDeleteEntireSchedule($s);
				return FALSE;
			}
		}
		foreach ($s->reservations as $r) {
			$eq_item = EqItem::getOneFromDb(['eq_item_id' => $r->eq_item_id], $s->dbConnection);
			if ($eq_item->matchesDb) {
				$eq_item->loadEqGroup();
				array_push($alertMessageData['item_names'], $eq_item->name);
			}

			if (!$r->doDelete()) {
				doRevertDeleteEntireSchedule($s);
				return FALSE;
			}
		}

		if (!$s->doDelete()) {
			doRevertDeleteEntireSchedule($s);
			return FALSE;
		}

		$s->doCreateQueuedMessages($eq_item->eq_group, $alertMessageData, 'flag_contact_on_reserve_cancel');
		return TRUE;
	}

	function doDeleteSingleTimeBlock($s, $actionVal) {
		$alertMessageData = array('item_names' => [], 'time_ranges' => []); // containing: items and times

		$eltToDelete = TimeBlock::getOneFromDb(['time_block_id' => $actionVal], $s->dbConnection);

		if ($eltToDelete->matchesDb) {
			if (!$eltToDelete->getEqGroup()) {
				return FALSE;
			}
			$start_datetime = new DateTime($eltToDelete->start_datetime);
			$end_datetime   = new DateTime($eltToDelete->end_datetime);
			array_push($alertMessageData['time_ranges'], $start_datetime->format('Y-m-d H:i A') . ' to ' . $end_datetime->format('Y-m-d H:i A'));
			array_push($alertMessageData['item_names'], 'n/a');
		}

		if (!$eltToDelete->doDelete()) {
			return FALSE;
		}

		$s->doCreateQueuedMessages($eltToDelete->eq_group, $alertMessageData, 'flag_contact_on_reserve_cancel');
		return TRUE;
	}

	function doDeleteSingleReservation($s, $actionVal) {
		$alertMessageData = array('item_names' => [], 'time_ranges' => []); // containing: items and times

		$eltToDelete = Reservation::getOneFromDb(['reservation_id' => $actionVal], $s->dbConnection);

		if ($eltToDelete->matchesDb) {
			if (!$eltToDelete->getEqGroup()) {
				return FALSE;
			}
			array_push($alertMessageData['time_ranges'], 'n/a');
			array_push($alertMessageData['item_names'], $eltToDelete->eq_item->name);
		}

		if (!$eltToDelete->doDelete()) {
			return FALSE;
		}

		$s->doCreateQueuedMessages($eltToDelete->eq_group, $alertMessageData, 'flag_contact_on_reserve_cancel');
		return TRUE;
	}

	function doRevertDeleteEntireSchedule($s) {
		foreach ($s->time_blocks as $tb) {
			$tb->flag_delete = FALSE;
			$tb->updateDb();
		}
		foreach ($s->reservations as $r) {
			$r->flag_delete = FALSE;
			$r->updateDb();
		}
		$s->flag_delete = FALSE;
		$s->updateDb();
	}

	#------------------------------------------------#

	$results = [
		'status' => 'failure'
	];

	if (!$schedule) {
		$results['notes'] = 'no schedule provided with form submission';
		echo json_encode($results);
		exit;
	}

	// make sure the specified schedule exists
	$SCHED = Schedule::getOneFromDb(['schedule_id' => $schedule], $DB);
	if (!($SCHED->matchesDb)) {
		$results['notes'] = 'requested schedule does not exist';
		echo json_encode($results);
		exit;
	}

	// check user access to the schedule
	if (!((($USER->flag_is_system_admin) || ($USER->user_id == $SCHED->user_id)))) {
		$SCHED->loadReservationsDeeply();
		if (count($SCHED->reservations) < 1) {
			$results['notes'] = 'requested schedule has no reservations associated with it';
			echo json_encode($results);
			exit;
		}
		if (!$USER->canManageEqGroup($SCHED->reservations[0]->eq_item->eq_group)) {
			$results['notes'] = 'user lacks permission to this group';
			echo json_encode($results);
			exit;
		}
	}

	//###############################################################
	if ($action == 'updateNotes') {
		$SCHED->notes = htmlentities($actionVal);
		$SCHED->updateDb();
		if ($SCHED->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'updateType') {
		if ($actionVal == 'manager') {
			$SCHED->type = 'manager';
		}
		elseif ($actionVal == 'consumer') {
			$SCHED->type = 'consumer';
		}
		else {
			$results['notes'] = 'requested action value does not conform to expected values';
			echo json_encode($results);
			exit;
		}
		$SCHED->updateDb();
		if ($SCHED->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	elseif ($action == 'deleteSchedule') {
		if (doDeleteEntireSchedule($SCHED)) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
	//	# TODO - DKC says: This condition appears to be obsolete (it mimics the 'deleteReservation' action, below)
	//	elseif ($action == 'deleteEqItem') {
	//		$SCHED->loadReservations();
	//		if (count($SCHED->reservations) == 1) {
	//			if ($SCHED->reservations[0]->eq_item_id == $actionVal) {
	//				if (doDeleteEntireSchedule($SCHED)) {
	//					$results['status'] = 'success';
	//				}
	//			}
	//		}
	//		else {
	//			$RESV = Reservation::getOneFromDb(['schedule_id' => $SCHED->schedule_id, 'eq_item_id' => $actionVal], $DB);
	//			if ($RESV->matchesDb) {
	//				$RESV->doDelete();
	//				if ($RESV->matchesDb) {
	//					$results['status'] = 'success';
	//				}
	//			}
	//		}
	//	}
	//###############################################################
	elseif ($action == 'deleteReservation') {
		$SCHED->loadReservations();
		if (count($SCHED->reservations) == 1) {
			if ($SCHED->reservations[0]->reservation_id == $actionVal) {
				if (doDeleteEntireSchedule($SCHED)) {
					$results['status'] = 'success';
				}
			}
		}
		else {
			if (doDeleteSingleReservation($SCHED, $actionVal)) {
				$results['status'] = 'success';
			}
		}
	}
	//###############################################################
	elseif ($action == 'deleteTimeBlock') {
		$SCHED->loadTimeBlocks();
		if (count($SCHED->time_blocks) == 1) {
			if ($SCHED->time_blocks[0]->time_block_id == $actionVal) {
				if (doDeleteEntireSchedule($SCHED)) {
					$results['status'] = 'success';
				}
			}
			else {
				$results['notes'] = 'tried to remove a time block that is not a part of this schedule';
			}
		}
		else {
			if (doDeleteSingleTimeBlock($SCHED, $actionVal)) {
				$results['status'] = 'success';
			}
		}
	}

	echo json_encode($results);
?>