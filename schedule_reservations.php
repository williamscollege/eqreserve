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

	# How to schedule the reservation(s)
	$bitAllDay                 = htmlentities((isset($_REQUEST["isAllDayEvent"])) ? 1 : 0);
	$strReservationType        = htmlentities((isset($_REQUEST["reservationType"])) ? 'manager' : 'consumer');
	$dateReservationStartDate  = htmlentities((isset($_REQUEST["reservationStartDate"])) ? $_REQUEST["reservationStartDate"] : 0);
	$dateReservationStartTime  = htmlentities((isset($_REQUEST["reservationStartTimeConverted"])) ? $_REQUEST["reservationStartTimeConverted"] : 0);
	$dateComputedStartDateTime = htmlentities(!$bitAllDay ? ($dateReservationStartDate . ' ' . $dateReservationStartTime) : $dateReservationStartDate . ' 00:00:00');
	$dateReservationEndDate    = htmlentities((isset($_REQUEST["reservationEndDate"])) ? $_REQUEST["reservationEndDate"] : 0);
	$dateReservationEndTime    = htmlentities((isset($_REQUEST["reservationEndTimeConverted"])) ? $_REQUEST["reservationEndTimeConverted"] : 0);
	$dateComputedEndDateTime   = htmlentities(!$bitAllDay ? $dateReservationEndDate . ' ' . $dateReservationEndTime : $dateReservationEndDate . ' 23:59:00');
	$strRepeatFrequencyType    = htmlentities((isset($_REQUEST["repeatFrequencyType"])) ? util_quoteSmart($_REQUEST["repeatFrequencyType"]) : 0);
	$intRepeatInterval         = htmlentities((isset($_REQUEST["repeatInterval"])) ? $_REQUEST["repeatInterval"] : 0);
	$strRepeatEndType          = htmlentities((isset($_REQUEST["repeatEndType"])) ? util_quoteSmart($_REQUEST["repeatEndType"]) : 0);
	$intRepeatEndOnQuantity    = htmlentities((isset($_REQUEST["repeatEndOnQuantity"])) ? $_REQUEST["repeatEndOnQuantity"] : 0);
	$dateRepeatEndOnDate       = htmlentities((isset($_REQUEST["repeatEndOnDate"])) ? $_REQUEST["repeatEndOnDate"] : 0);
	$strReservationSummaryText = htmlentities((isset($_REQUEST["reservationSummaryText"])) ? util_quoteSmart($_REQUEST["reservationSummaryText"]) : 0);


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure'
	];


	#------------------------------------------------#
	# Conflict checks
	#------------------------------------------------#
	# TODO: conflict checks in function for single, weekly, monthly inserts:


	#------------------------------------------------#
	# Insert 1 Schedule
	#------------------------------------------------#
	$sched = New Schedule(['DB' => $DB]);

	$sched->type            = $strReservationType;
	$sched->user_id         = $USER->user_id;
	$sched->frequency_type  = $strRepeatFrequencyType;
	$sched->repeat_interval = $intRepeatInterval;
	# TODO : need to derive this, above...
	# $sched->list_days       = ;
	$sched->start_time      = $dateComputedStartDateTime;
	$sched->end_time        = $dateComputedEndDateTime;
	$sched->end_on_type     = $strRepeatEndType;
	$sched->end_on_quantity = $intRepeatEndOnQuantity;
	$sched->end_on_date     = $dateRepeatEndOnDate;
	$sched->summary         = $strReservationSummaryText;
	$sched->flag_all_day    = $bitAllDay;
	# $sched->notes           = ""; # field is not used in this context

	$sched->updateDb();

	if (!$sched->matchesDb) {
		// error: no matching record found
		# TODO : error redirecting: "Warning: Cannot modify header information - headers already sent by... \util.php on line 38"
		#util_redirectToAppHome('failure', 60);
		exit;
	}

	#------------------------------------------------#
	# Insert X Reservation(s)
	#------------------------------------------------#
	foreach ($_REQUEST as $key => $val) {
		if (substr($key, 0, 9) == 'subgroup-') {
			# echo $key . ":" . $val . "<br />"; // test output
			# TODO : Caching Problem results if user hits browser back button, then re-submits form: a new schedule is created, but the old schedule_id is entered for reservations.
			$reserv = New Reservation(['DB' => $DB]);

			$reserv->eq_item_id  = $val;
			$reserv->schedule_id = $sched->schedule_id;

			$reserv->updateDb();
		}
	}

	#------------------------------------------------#
	# Insert Time Block(s)
	#------------------------------------------------#
	//###############################################################
	if ($strRepeatFrequencyType == 'no_repeat') {
		# Insert 1 Time Block
		$timeblock = New TimeBlock(['DB' => $DB]);

		$timeblock->schedule_id = $sched->schedule_id;
		$timeblock->start_time  = $dateComputedStartDateTime;
		$timeblock->end_time    = $dateComputedEndDateTime;

		$timeblock->updateDb();


		# Output
		$results['status'] = 'success';
	}
	//###############################################################
	elseif ($strRepeatFrequencyType == 'weekly') {
		# Insert X Time Block


	}
	//###############################################################
	elseif ($strRepeatFrequencyType == 'monthly') {
		# Insert X Time Block
		# TODO : we may be able to combine the weekly and monthly else statements into a single statement


	}
	###############################################################

?>