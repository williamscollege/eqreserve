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
	# Schedule the reservation(s)
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

	# Which days to repeat (if any)
	$dow_tags   = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
	$repeat_dow = array();
	foreach ($dow_tags as $dow) {
		$repeat_dow[$dow] = htmlentities($_REQUEST['repeat_dow_' . $dow]);
	}
	$repeat_dom = array();
	for ($dom = 1; $dom <= 31; $dom++) {
		$repeat_dom[$dom] = htmlentities($_REQUEST['repeat_dom_' . $dom]); // 1 through 31
	}
	$strRepeat_dow = "'" . implode("','", $repeat_dow) . "'";
	$strRepeat_dom = "'" . implode("','", $repeat_dom) . "'";


	# Insert X Time Block
	# loop: StartDate to EndDate
	# frequency where x = weekly, monthly [insert which days]
	# TODO DateChecks: StartDate,EndDate >= NOW; EndDate >= StartDate;
	# IN PROCESS OF DEVELOPMENT....
//	for ($dateIncrement = $dateReservationStartDate; $dateIncrement <= $dateRepeatEndOnDate; ) {
//		# Insert 1 Time Block
//		$timeblock = New TimeBlock(['DB' => $DB]);
//
//		$timeblock->schedule_id = $sched->schedule_id;
//		$timeblock->start_time  = $dateComputedStartDateTime;
//		$timeblock->end_time    = $dateComputedEndDateTime;
//
//		$timeblock->updateDb();
//
//		$dateIncrement->add(new DatePeriod('P'));
//		# Increment by Interval
//		$dateIncrement .= $dateIncrement + $intRepeatInterval; // reset using DATE format!
//	}


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
	if ($strRepeatFrequencyType == 'weekly') {
		$sched->which_days = $strRepeat_dow;
	}
	elseif ($strRepeatFrequencyType == 'monthly') {
		$sched->which_days = $strRepeat_dom;
	}
	$sched->start_time      = $dateComputedStartDateTime;
	$sched->end_time        = $dateComputedEndDateTime;
	$sched->end_on_type     = $strRepeatEndType;
	$sched->end_on_quantity = $intRepeatEndOnQuantity;
	$sched->end_on_date     = $dateRepeatEndOnDate;
	$sched->summary         = $strReservationSummaryText;
	$sched->flag_all_day    = $bitAllDay;


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
		# loop: StartDate to EndDate
		# frequecy where x = weekly, monthly [insert which days]

	}
	//###############################################################
	elseif ($strRepeatFrequencyType == 'monthly') {
		# Insert X Time Block
		# TODO : we may be able to combine the weekly and monthly else statements into a single statement


	}
	###############################################################

?>