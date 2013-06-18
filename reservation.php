<?php
	$pageTitle = 'Reservation Requested';
	require_once('head.php');

	require_once('/classes/schedule.class.php');
	require_once('/classes/eq_group.class.php');
	require_once('/classes/eq_subgroup.class.php');
	require_once('/classes/eq_item.class.php');


	# SCRAP THIS OUTPUT
	echo "<pre>";
	print_r($_POST);
	echo "</pre>";

	#------------------------------------------------#
	# Fetch values
	#------------------------------------------------#
	# Things to reserve
	# TODO must derive these lists: list_days
	$strRepeat_dom_10  = htmlentities((isset($_REQUEST["repeat_dom_10"])) ? util_quoteSmart($_REQUEST["repeat_dom_10"]) : 0);
	$strRepeat_dow_mon = htmlentities((isset($_REQUEST["repeat_dow_mon"])) ? util_quoteSmart($_REQUEST["repeat_dow_mon"]) : 0);

	# TODO - this value is unnecessary!
	$intReservationGroupID = htmlentities((isset($_REQUEST["reservationGroupID"])) ? $_REQUEST["reservationGroupID"] : 0);

	# How to schedule the reserveation(s)
	$bitAllDay          = htmlentities((isset($_REQUEST["isAllDayEvent"])) ? 1 : 0);
	$strReservationType = htmlentities((isset($_REQUEST["reservationType"])) ? 'manager' : 'consumer');
	$dateReservationStartDate  = htmlentities((isset($_REQUEST["reservationStartDate"])) ? $_REQUEST["reservationStartDate"] : 0);
	$dateReservationStartTime  = htmlentities((isset($_REQUEST["reservationStartTimeConverted"])) ? $_REQUEST["reservationStartTimeConverted"] : 0);
	$dateComputedStartDateTime = htmlentities(!$bitAllDay ? ($dateReservationStartDate . ' ' . $dateReservationStartTime) : $dateReservationStartDate . ' 00:00:00');
	$dateReservationEndDate  = htmlentities((isset($_REQUEST["reservationEndDate"])) ? $_REQUEST["reservationEndDate"] : 0);
	$dateReservationEndTime  = htmlentities((isset($_REQUEST["reservationEndTimeConverted"])) ? $_REQUEST["reservationEndTimeConverted"] : 0);
	$dateComputedEndDateTime = htmlentities(!$bitAllDay ? $dateReservationEndDate . ' ' . $dateReservationEndTime : $dateReservationEndDate . ' 23:59:00');
	$strRepeatFrequencyType = htmlentities((isset($_REQUEST["repeatFrequencyType"])) ? util_quoteSmart($_REQUEST["repeatFrequencyType"]) : 0);
	$intRepeatInterval      = htmlentities((isset($_REQUEST["repeatInterval"])) ? $_REQUEST["repeatInterval"] : 0);
	$strRepeatEndType       = htmlentities((isset($_REQUEST["repeatEndType"])) ? util_quoteSmart($_REQUEST["repeatEndType"]) : 0);
	$intRepeatEndOnQuantity = htmlentities((isset($_REQUEST["repeatEndOnQuantity"])) ? $_REQUEST["repeatEndOnQuantity"] : 0);
	$dateRepeatEndOnDate    = htmlentities((isset($_REQUEST["repeatEndOnDate"])) ? $_REQUEST["repeatEndOnDate"] : 0);
	$strReservationSummaryText = htmlentities((isset($_REQUEST["reservationSummaryText"])) ? util_quoteSmart($_REQUEST["reservationSummaryText"]) : 0);


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure'
	];


	#------------------------------------------------#
	# Identify and process requested action
	#------------------------------------------------#
	//###############################################################
	if ($strRepeatFrequencyType == 'no_repeat') {
		# [Summary: Insert 1 Schedule, X Reservations, 1 Time Block]

		# Insert 1 Schedule
		$sched = New Schedule(['DB' => $DB]);

		$sched->type            = $strReservationType;
		$sched->user_id         = $USER->user_id;
		$sched->notes           = "weeee testing"; #hide
		$sched->frequency_type  = $strRepeatFrequencyType;
		$sched->interval        = $intRepeatInterval;
		$sched->list_days       = "1"; # default #hide
		$sched->start_time      = $dateComputedStartDateTime;
		$sched->end_time        = $dateComputedEndDateTime;
		$sched->end_on_type     = $strRepeatEndType;
		$sched->end_on_quantity = $intRepeatEndOnQuantity;
		$sched->end_on_date     = $dateRepeatEndOnDate;
		$sched->summary         = $strReservationSummaryText;
		$sched->flag_all_day    = 0; //$bitAllDay;
		$sched->flag_delete     = 0; #hide


		$sched->updateDb();

		//		$sched->schedule_id;

		//		if (!$sched->matchesDb) {
		//			// error: no matching record found
		//			util_redirectToAppHome('failure', 60);
		//			exit;
		//		}

		# Insert X Reservations
		//		foreach ($_REQUEST as $key => $val) {
		//if (substr($key, 0, 10) == 'subgroup-') {

		$reserv = New Reservation(['DB' => $DB]);

		$reserv->eq_item     = 403;
		$reserv->schedule_id = $sched->schedule_id;

		$reserv->updateDb();
		//}
		//		}

		# Insert 1 Time Block
		$timeblock = New TimeBlock(['DB' => $DB]);

		$timeblock->schedule_id = $sched->schedule_id;
		$timeblock->start_time  = $dateComputedStartDateTime;
		$timeblock->end_time    = $dateComputedEndDateTime;

		# TODO: conflict checks, and below... weekly, monthly inserts

		# Output
		$results['status'] = 'success';
	}
	//###############################################################
	elseif ($strRepeatFrequencyType == 'weekly') {
		# Summary: Insert 1 Schedule, X Reservations, X Time Blocks


	}
	//###############################################################
	elseif ($strRepeatFrequencyType == 'monthly') {
		# Summary: Insert 1 Schedule, X Reservations, X Time Blocks


	}
	###############################################################

?>