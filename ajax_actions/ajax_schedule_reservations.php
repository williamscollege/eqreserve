<?php
	require_once('head_ajax.php');

	require_once('../classes/schedule.class.php');
	require_once('../classes/eq_group.class.php');
	require_once('../classes/eq_subgroup.class.php');
	require_once('../classes/eq_item.class.php');

	# SCRAP THIS OUTPUT
	echo "<pre>";
	print_r($_REQUEST);
	echo "</pre>";

	# TODO Problem: Caching Problem results if user hits browser back button, then re-submits form: a new schedule is created, but the old schedule_id is entered for reservations.
	# TODO Solution: Generate unique id key on schedule php page; on server side, check for existence of that key in [db table];
	#   if not exists, proceed. if exists, then pull values from db, refresh page in edit mode (or clear page), create new unique key, and post confirmation dialog to continue; update db record with new key


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure'
	];


	#------------------------------------------------#
	# Fetch values
	#------------------------------------------------#
	# form values
	# TODO: add a $confirmConflictOverrideFlag (name?) form value
	$intEqGroupID               = isset($_REQUEST["eqGroupID"]) ? $_REQUEST["eqGroupID"] : 0;
	$strScheduleType            = htmlentities((isset($_REQUEST["scheduleIsTypeManager"])) ? 'manager' : 'consumer');
	$strScheduleFrequencyType   = htmlentities((isset($_REQUEST["scheduleFrequencyType"])) ? util_quoteSmart($_REQUEST["scheduleFrequencyType"]) : 0);
	$intScheduleRepeatInterval  = isset($_REQUEST["scheduleRepeatInterval"]) ? $_REQUEST["scheduleRepeatInterval"] : 0;
	$dateScheduleTimeBlockStart = htmlentities((isset($_REQUEST["scheduleStartTimeConverted"])) ? $_REQUEST["scheduleStartTimeConverted"] : 0);
	$strScheduleDuration        = htmlentities((isset($_REQUEST["scheduleDuration"])) ? $_REQUEST["scheduleDuration"] : 0);
	$dateScheduleStartOnDate    = htmlentities((isset($_REQUEST["scheduleStartOnDate"])) ? $_REQUEST["scheduleStartOnDate"] : 0);
	$dateScheduleEndOnDate      = htmlentities((isset($_REQUEST["scheduleEndOnDate"])) ? $_REQUEST["scheduleEndOnDate"] : 0);
	$strScheduleSummaryText     = htmlentities((isset($_REQUEST["scheduleSummaryText"])) ? util_quoteSmart($_REQUEST["scheduleSummaryText"]) : 0);
	$strScheduleNotes           = htmlentities((isset($_REQUEST["scheduleNotes"])) ? util_quoteSmart($_REQUEST["scheduleNotes"]) : 0);

	# fetch repeating days (if any)
	$repeat_dow = array();
	$repeat_dom = array();
	if ($strScheduleFrequencyType == 'no_repeat') {
		$strWhichDays = 'none';
	}
	elseif ($strScheduleFrequencyType == 'weekly') {
		$dow_tags = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
		foreach ($dow_tags as $dow) {
			if (htmlentities($_REQUEST['repeat_dow_' . $dow]) == 1) {
				$repeat_dow[] = $dow;
			}
		}
		$strWhichDays = implode(",", $repeat_dow);
	}
	elseif ($strScheduleFrequencyType == 'monthly') {
		for ($dom = 1; $dom <= 31; $dom++) {
			if (htmlentities($_REQUEST['repeat_dom_' . $dom]) == 1) {
				$repeat_dom[] = $dom;
			}
		}
		$strWhichDays = implode(",", $repeat_dom);
	}


	#------------------------------------------------#
	# Access Validation checks
	#------------------------------------------------#
	# check for existence of eqGroupID
	$eq_group = EqGroup::getOneFromDb(['eq_group_id' => $intEqGroupID], $DB);
	if (!$eq_group->matchesDb) {
		$results['note'] = 'equipment group does not exist or was deleted';
		echo json_encode($results);
		exit;
	}

	# user access validation - is this user allowed to add a schedule of this type for this group?
	# check if user is a system admin or manager of this group
	if (!$USER->canManageEqGroup($eq_group)) {
		# check if non-manager user is attempting to schedule as a manager
		if ($strScheduleType == 'manager') {
			$results['note'] = 'cannot create manager reservation - not a manager of this group';
			echo json_encode($results);
			exit;
		}
	}

	# check if user has consumer access to this group
	if (!$USER->canUseEqGroup($eq_group)) {
		$results['note'] = 'cannot create user reservation - not a user of this group';
		echo json_encode($results);
		exit;
	}


	#------------------------------------------------#
	# Data Validation checks
	#------------------------------------------------#

	# check if duration time matches expected format
	$legalDurations = ['5M', '10M', '15M', '20M', '30M', '45M', '60M', '90M', '2H', '3H', '4H', '5H', '6H', '7H', '8H', '16H', '1D', '2D', '3D', '4D', '5D', '6D', '7D', '14D', '28D'];
	if (!in_array($strScheduleDuration, $legalDurations)) {
		$results['note'] = 'invalid time format for duration value';
		echo json_encode($results);
		exit;
	}

	# TODO: NOTE this is obsolete as the EqGroup::getOneFromDb above returns false if eq_group.flag_delete = true
	# check that eq_group is active
	if ($eq_group->flag_delete) {
		# Note: In actual practice, the action has already redirected to homepage before reaching this point [util_redirectToAppHome('failure', 50)]
		$results['note'] = 'unable to create reservation for deleted group';
		echo json_encode($results);
		exit;
	}

	# check that eq_subgroup and eq_item is active
	foreach ($_REQUEST as $key => $val) {
		if (substr($key, 0, 9) == 'subgroup-') {
			$eq_item = EqItem::getOneFromDb(['eq_item_id' => $val], $DB);
			if ($eq_item->flag_delete) {
				$results['note'] = 'unable to create reservation for deleted item';
				echo json_encode($results);
				exit;
			}
			$eq_subgroup = EqSubgroup::getOneFromDb(['eq_subgroup_id' => $eq_item->eq_subgroup_id], $DB);
			if ($eq_subgroup->flag_delete) {
				$results['note'] = 'unable to create reservation for deleted subgroup';
				echo json_encode($results);
				exit;
			}
		}
	}


	#------------------------------------------------#
	# Insert 1 Schedule
	#------------------------------------------------#
	$sched = New Schedule(['DB' => $DB]);

	$sched->type                 = $strScheduleType;
	$sched->user_id              = $USER->user_id;
	$sched->frequency_type       = $strScheduleFrequencyType;
	$sched->repeat_interval      = $intScheduleRepeatInterval;
	$sched->which_days           = $strWhichDays;
	$sched->timeblock_start_time = $dateScheduleTimeBlockStart;
	$sched->timeblock_duration   = $strScheduleDuration;
	$sched->start_on_date        = $dateScheduleStartOnDate;
	$sched->end_on_date          = $dateScheduleEndOnDate;
	$sched->summary              = $strScheduleSummaryText;
	$sched->notes                = $strScheduleNotes;


	$sched->updateDb();

	if (!$sched->matchesDb) {
		// error: no matching record found
		$results['note'] = 'unable to create schedule';
		echo json_encode($results);
		exit;
	}

	#------------------------------------------------#
	# Insert X Reservation(s)
	#------------------------------------------------#
	foreach ($_REQUEST as $key => $val) {
		if (substr($key, 0, 9) == 'subgroup-') {
			# echo $key . ":" . $val . "<br />"; // test output
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
	// TODO: Check if weekly or monthly repeating event has zero timeblocks (ie user chose 'Repeat 1 Time' and 'Monday', but today is Wednesday; thus, nothing will be reserved.)

	# initialize variables
	$start_date                 = new DateTime($dateScheduleStartOnDate);
	$end_date                   = new DateTime($dateScheduleEndOnDate);
	$count_weeks                = 0;
	$count_months               = 0;
	$dateScheduleTimeBlockStart = new DateTime($dateScheduleTimeBlockStart);
	$flag_initial_day           = TRUE;

	// data validation
	if ($strScheduleFrequencyType == 'no_repeat') {
		# careful: must set by value, not by reference
		$end_date = new DateTime($dateScheduleStartOnDate);
	}

	# increment one day at a time, starting with initial day, going until end day
	for ($cur_date = $start_date; $cur_date <= $end_date; $cur_date->add(new DateInterval('P1D'))) {

//		# temporary test output
//		echo $cur_date->format('Y-m-d') . "<br>";
//		echo $end_date->format('Y-m-d') . "<br>";
//		echo $cur_date->format('D') . "<br>";
//		echo $cur_date->format('j') . "<hr>";

		if (!$flag_initial_day) {
			# increment week counter iff appropriate (each time $cur_date passes sunday)
			# D	= a textual representation of a day, three letters (Mon through Sun)
			if ($cur_date->format('D') == 'Mon') {
				# determine Mon through Sun
				$count_weeks += 1;
				//				echo "week_counter=" . $count_weeks . "<br>";
			}

			# increment month_counter iff appropriate (each time $cur_date passes 1st of month)
			# j = day of the month without leading zeros (1 to 31)
			if ($cur_date->format('j') == '1') {
				$count_months += 1;
				//				echo "month_counter=" . $count_months . "<br>";
			}
		}
		$flag_initial_day = FALSE;

		if (passesFilter($cur_date, $strScheduleFrequencyType, $intScheduleRepeatInterval, $repeat_dow, $repeat_dom, $count_weeks, $count_months)) {
			# Calculate datetime for time_blocks (php.net: 'DateInterval::format')
			$dateTimeBlockStartDateTime = new DateTime($cur_date->format('Y-m-d') . $dateScheduleTimeBlockStart->format('H:i:s'));
			$dateTimeBlockEndDateTime   = new DateTime($dateTimeBlockStartDateTime->format('Y-m-d H:i:s'));

			# add duration
			if (strpos($strScheduleDuration, 'M')) {
				# minutes
				$dateTimeBlockEndDateTime->add(new DateInterval('PT' . $strScheduleDuration));
			}
			elseif (strpos($strScheduleDuration, 'H')) {
				# hours
				$dateTimeBlockEndDateTime->add(new DateInterval('PT' . $strScheduleDuration));
			}
			elseif (strpos($strScheduleDuration, 'D')) {
				# days
				$dateTimeBlockEndDateTime->add(new DateInterval('P' . $strScheduleDuration));
			}

			# Create time block
			$timeblock = New TimeBlock(['DB' => $DB]);

			# Assign properties
			$timeblock->schedule_id    = $sched->schedule_id;
			$timeblock->start_datetime = $dateTimeBlockStartDateTime->format('Y-m-d H:i:s');
			$timeblock->end_datetime   = $dateTimeBlockEndDateTime->format('Y-m-d H:i:s');

			# Update
			$timeblock->updateDb();

			# Output
			$results['status'] = 'success';
		}
	}

	function passesFilter($cur, $repeat_type, $interval, $repeat_dow, $repeat_dom, $cnt_weeks, $cnt_months) {
		if ($repeat_type == 'no_repeat') {
			return TRUE;
		}
		elseif ($repeat_type == 'weekly') {
			# array format: mon,wed,fri
			# D	= a textual representation of a day, three letters (Mon through Sun)
			# convert PHP formatted date value to lowercase to check equality with the lowercase value returned from HTML form
			if (in_array(strtolower($cur->format('D')), $repeat_dow)) {
				if ($cnt_weeks % $interval == 0) {
					return TRUE;
				}
			}
		}
		elseif ($repeat_type == 'monthly') {
			# array format: 1,4,9,16,30
			# j = day of the month without leading zeros (1 to 31)
			if (in_array($cur->format('j'), $repeat_dom)) {
				if ($cnt_months % $interval == 0) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}


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


	echo json_encode($results);

?>