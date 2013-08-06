<?php
	require_once('../classes/schedule.class.php');
	require_once('../classes/eq_group.class.php');
	require_once('../classes/eq_subgroup.class.php');
	require_once('../classes/eq_item.class.php');

    require_once('head_ajax.php');

	# SCRAP THIS OUTPUT
	echo "<pre>";
	print_r($_POST);
	echo "</pre>";


    #------------------------------------------------#
    # Set default return value
    #------------------------------------------------#
    $results = [
        'status' => 'failure'
    ];

    #------------------------------------------------#
    # Access Validation checks
    #------------------------------------------------#
    # TODO: user access validation - is this user allowed to add a schedule of this type for this group?
	# get group ID from _REQUEST
	$intScheduleGroupID  		= isset($_REQUEST["scheduleGroupID"]) ? $_REQUEST["scheduleGroupID"] : 0;
    # check that the user has management access - if so, OK to continue, else abort
    #   else if user does NOT have management access and type is manager - if so, abort
    #   else if the user has consumer access - if so, OK to continue, else abort


    #------------------------------------------------#
	# Fetch values
	#------------------------------------------------#
	# Form values
    # TODO: add a $confirmConflictOverrideFlag (name?) form value
	$strScheduleType            = htmlentities((isset($_REQUEST["scheduleType"])) ? 'manager' : 'consumer');
	$strScheduleFrequencyType   = htmlentities((isset($_REQUEST["scheduleFrequencyType"])) ? util_quoteSmart($_REQUEST["scheduleFrequencyType"]) : 0);
	$intScheduleRepeatInterval  = isset($_REQUEST["scheduleRepeatInterval"]) ? $_REQUEST["scheduleRepeatInterval"] : 0;
	$dateScheduleTimeBlockStart = htmlentities((isset($_REQUEST["scheduleStartTimeConverted"])) ? $_REQUEST["scheduleStartTimeConverted"] : 0);
	$strScheduleDuration        = htmlentities((isset($_REQUEST["scheduleDuration"])) ? $_REQUEST["scheduleDuration"] : 0);
	$dateScheduleStartOnDate    = htmlentities((isset($_REQUEST["scheduleStartOnDate"])) ? $_REQUEST["scheduleStartOnDate"] : 0);
	$dateScheduleEndOnDate      = htmlentities((isset($_REQUEST["scheduleEndOnDate"])) ? $_REQUEST["scheduleEndOnDate"] : 0);
	$strScheduleSummaryText     = htmlentities((isset($_REQUEST["scheduleSummaryText"])) ? util_quoteSmart($_REQUEST["scheduleSummaryText"]) : 0);

	# Which_Days: Days of Week
	$dow_tags   = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
	$repeat_dow = array();
	foreach ($dow_tags as $dow) {
		$repeat_dow[$dow] = htmlentities($_REQUEST['repeat_dow_' . $dow]);
	}
	# Which_Days: Days of Month
	$repeat_dom = array();
	for ($dom = 1; $dom <= 31; $dom++) {
		$repeat_dom[$dom] = htmlentities($_REQUEST['repeat_dom_' . $dom]); // 1 through 31
	}
	$strScheduleWhichDays_dow = "'" . implode("','", $repeat_dow) . "'";
	$strScheduleWhichDays_dom = "'" . implode("','", $repeat_dom) . "'";

	# TimeBlocks: Start/End DateTime
	$dateTimeBlockStartDateTime = new DateTime($dateScheduleStartOnDate . ' ' . $dateScheduleTimeBlockStart);
	$dateTimeBlockEndDateTime   = new DateTime($dateTimeBlockStartDateTime->format('Y-m-d H:i:s'));
	# Time periods (see: http://us3.php.net/manual/en/dateinterval.format.php)
	if (strpos($strScheduleDuration, 'M')){
		# Minutes
		$dateTimeBlockEndDateTime->add(new DateInterval('P0Y0M0DT0H' . $strScheduleDuration . '0S'));
	}
	elseif (strpos($strScheduleDuration, 'H')){
		# Hours
		$dateTimeBlockEndDateTime->add(new DateInterval('P0Y0M0DT' . $strScheduleDuration . '0M0S'));
	}
	elseif (strpos($strScheduleDuration, 'DT')){
		# Days
		$dateTimeBlockEndDateTime->add(new DateInterval('P0Y0M' . $strScheduleDuration . '0H0M0S'));
	}

	#------------------------------------------------#
	# Validation checks (EndDate >= NOW, StartDate>=EndDate, etc.)
	#------------------------------------------------#
    # TODO: data validation checks


    # TODO: PDO start transaction

	#------------------------------------------------#
	# Insert 1 Schedule
	#------------------------------------------------#
	$sched = New Schedule(['DB' => $DB]);

	$sched->type            = $strScheduleType;
	$sched->user_id         = $USER->user_id;
	$sched->frequency_type  = $strScheduleFrequencyType;
	$sched->repeat_interval = $intScheduleRepeatInterval;
	if ($strScheduleFrequencyType == 'weekly') {
		$sched->which_days = $strScheduleWhichDays_dow;
	}
	elseif ($strScheduleFrequencyType == 'monthly') {
		$sched->which_days = $strScheduleWhichDays_dom;
	}
	else { # no_repeat
		$sched->which_days = 1;
	}
	$sched->timeblock_start_time = $dateScheduleTimeBlockStart;
	$sched->timeblock_duration   = $strScheduleDuration;
	$sched->start_on_date        = $dateScheduleStartOnDate;
	$sched->end_on_date          = $dateScheduleEndOnDate;
	$sched->summary              = $strScheduleSummaryText;


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

    // TODO: figure out how to handle / account for manager vs consumer reservations
    // TODO: figure out structure/process to handle alerts for over-ridden reservations

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


    #------------------------------------------------#
    # Conflict checks
    #------------------------------------------------#
    # TODO: conflict checks in function for single, weekly, monthly inserts:

    #   if conflict
    #      if override flag set
    #         delete existing conflicts
    #         for each one so deleted, add alert to the email queue for that user saying their reservation has been overridden (incl info about this schedule (notes, user, ?))
    #         commit
    #         results status = success
    #      else
    #         store the list of conflicts in the result object (include the type of the conflicting reservation/time-block/schedule)
    #         store the type of this schedule in the results (manager vs consumer) - NOTE: re-submit w/ override will be handled by the form page as needed
    #         rollback
    #   else
    #     commit
    #     results status = success


    #------------------------------------------------#
    # Queue Email Alerts
    #------------------------------------------------#

    # get all managers of the group
    # for each manager:
    #   get their comm prefs for the group
    #   if flag_contact_on_reserve_create, queue an email alert to that manager about these reservations


	# Below is older stuff, to be replaced with the newer, above, later
	//###############################################################
	if ($strScheduleFrequencyType == 'no_repeat') {
		# Insert 1 Time Block
		$timeblock = New TimeBlock(['DB' => $DB]);

		$timeblock->schedule_id    = $sched->schedule_id;
		$timeblock->start_datetime = $dateTimeBlockStartDateTime->format('Y-m-d H:i:s');
		$timeblock->end_datetime   = $dateTimeBlockEndDateTime->format('Y-m-d H:i:s');

		$timeblock->updateDb();


		# Output
		$results['status'] = 'success';
	}
	//###############################################################
	elseif ($strScheduleFrequencyType == 'weekly') {
		# Insert X Time Blocks

		//		$start = DateTime::createFromFormat("Y-m-d H:i:s",$dateScheduleStartOnDate,new DateTimeZone("America/Toronto"));
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
		//			$timeblock->start_datetime  = $dateTimeBlockStartDateTime->format('Y-m-d H:i:s');;
		//			$timeblock->end_datetime    = $dateTimeBlockEndDateTime->format('Y-m-d H:i:s');;
		//
		//			$timeblock->updateDb();
		//		}
		//
		//		#END TEST
		//
		//		$dateIncrement->add(new DatePeriod('P'));
		//				# Increment by Interval
		//				$dateIncrement .= $dateIncrement + $intScheduleRepeatInterval; // reset using DATE format!
		//			}

	}
	//###############################################################
	elseif ($strScheduleFrequencyType == 'monthly') {
		# Insert X Time Block
		# TODO : we may be able to combine the weekly and monthly else statements into a single statement


	}
	###############################################################




?>