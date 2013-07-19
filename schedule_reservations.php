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
	$strReservationType        = htmlentities((isset($_REQUEST["reservationType"])) ? 'manager' : 'consumer');
	$dateReservationStartDate  = htmlentities((isset($_REQUEST["reservationStartDate"])) ? $_REQUEST["reservationStartDate"] : 0);
	$dateReservationStartTime  = htmlentities((isset($_REQUEST["reservationStartTimeConverted"])) ? $_REQUEST["reservationStartTimeConverted"] : 0);
	$dateComputedStartDateTime = $dateReservationStartDate . ' ' . $dateReservationStartTime;
	#TODO: $dateComputedEndDateTime   = $dateComputedStartDateTime + duration;
	$strRepeatFrequencyType    = htmlentities((isset($_REQUEST["repeatFrequencyType"])) ? util_quoteSmart($_REQUEST["repeatFrequencyType"]) : 0);
	$intRepeatInterval         = htmlentities((isset($_REQUEST["repeatInterval"])) ? $_REQUEST["repeatInterval"] : 0);
	$dateRepeatEndOnDate       = htmlentities((isset($_REQUEST["repeatEndOnDate"])) ? $_REQUEST["repeatEndOnDate"] : 0);
	$strReservationSummaryText = htmlentities((isset($_REQUEST["reservationSummaryText"])) ? util_quoteSmart($_REQUEST["reservationSummaryText"]) : 0);

	# Which days to repeat (if any)
	# Days of Week
	$dow_tags   = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
	$repeat_dow = array();
	foreach ($dow_tags as $dow) {
		$repeat_dow[$dow] = htmlentities($_REQUEST['repeat_dow_' . $dow]);
	}
	# Days of Month
	$repeat_dom = array();
	for ($dom = 1; $dom <= 31; $dom++) {
		$repeat_dom[$dom] = htmlentities($_REQUEST['repeat_dom_' . $dom]); // 1 through 31
	}
	$strRepeat_dow = "'" . implode("','", $repeat_dow) . "'";
	$strRepeat_dom = "'" . implode("','", $repeat_dom) . "'";


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure'
	];


	#------------------------------------------------#
	# Validation checks (EndDate >= NOW, StartDate>=EndDate, etc.)
	#------------------------------------------------#
	# TODO: validation checks


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
	$sched->timeblock_start_time      = $dateComputedStartDateTime;
	$sched->timeblock_duration   = $DKC_TODO_duration;
	$sched->start_on_date      = $dateComputedStartDateTime;
	$sched->end_on_date     = $dateRepeatEndOnDate;
	$sched->summary         = $strReservationSummaryText;


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

	# one day at a time, starting with initial day, going until end day
	#    if repeat type == no repeat, then end day = start day
	# week counter = 1
	# month counter = 1
	# for ($cur day = start date; cur day < end date; cur day ++ [note: this will likely use DateAdd() fxn])
	#   if (cur day passes filter (cur day, week day filter, month day filter, week counter, month counter, weeks interval, months interval) )
	#       create time block
	#   incr week counter iff appropriate (each time cur day passes sunday)
	#   incr month counter iff appropriate (each time cur day passes 1st of month)

	# function passes filter:
	#    if repeat == none
	#        return true
	#    if repeat == day of week
	#        return (week_day_filter[cur day day of week] (where week day filter is an associative array)) && (week counter % weeks interval == 0)
	#    if repeat == day of month
	#        return (month_day_filter[cur day day of month] (where month day filter is an associative array)) && (month counter % month interval == 0)
	#    return false


	# Below is older stuff, to be replaced with the newer, above, later
	//###############################################################
	if ($strRepeatFrequencyType == 'no_repeat') {
		# Insert 1 Time Block
		$timeblock = New TimeBlock(['DB' => $DB]);

		$timeblock->schedule_id = $sched->schedule_id;
		$timeblock->start_datetime  = $dateComputedStartDateTime;
		$timeblock->end_datetime    = $dateComputedEndDateTime;

		$timeblock->updateDb();


		# Output
		$results['status'] = 'success';
	}
	//###############################################################
	elseif ($strRepeatFrequencyType == 'weekly') {
		# Insert X Time Blocks

//		$start = DateTime::createFromFormat("Y-m-d H:i:s",$dateReservationStartDate,new DateTimeZone("America/Toronto"));
//		$end = ...;
//		# iterate through which_days for repeating days?
//
//		$interval = new DateInterval("P7D"); // 7 days
//		foreach($period as $dt){
//			#echo $dt->format("Y-m-d H:i:s") . "\n";
//
//			# Insert 1 Time Block
//			$timeblock = New TimeBlock(['DB' => $DB]);
//
//			$timeblock->schedule_id = $sched->schedule_id;
//			$timeblock->start_datetime  = $dateComputedStartDateTime;
//			$timeblock->end_datetime    = $dateComputedEndDateTime;
//
//			$timeblock->updateDb();
//		}
//
//		#END TEST
//
//		$dateIncrement->add(new DatePeriod('P'));
//				# Increment by Interval
//				$dateIncrement .= $dateIncrement + $intRepeatInterval; // reset using DATE format!
//			}

	}
	//###############################################################
	elseif ($strRepeatFrequencyType == 'monthly') {
		# Insert X Time Block
		# TODO : we may be able to combine the weekly and monthly else statements into a single statement


	}
	###############################################################

?>