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
	# UNUSED: $strAction = htmlentities((isset($_REQUEST["xyz"])) ? util_quoteSmart($_REQUEST["xyz"]) : 0);

	$bitAllDay           = htmlentities((isset($_REQUEST["isAllDayEvent"])) ? 1 : 0);
	$strReservationType  = htmlentities((isset($_REQUEST["reservationType"])) ? 'manager' : 'consumer');
	$dateRepeatEndOnDate = htmlentities((isset($_REQUEST["repeatEndOnDate"])) ? $_REQUEST["repeatEndOnDate"] : 0);

	# TODO must correctly create proper datetime
	$dateReservationEndDate     = htmlentities((isset($_REQUEST["reservationEndDate"])) ? $_REQUEST["reservationEndDate"] : 0);
	$dateReservationEndTime     = htmlentities((isset($_REQUEST["reservationEndTime"])) ? $_REQUEST["reservationEndTime"] : 0);
	$dateReservationEndDateTime = $dateReservationEndDate + $dateReservationEndTime;

	# TODO must correctly create proper datetime
	$dateReservationStartDate     = htmlentities((isset($_REQUEST["reservationStartDate"])) ? $_REQUEST["reservationStartDate"] : 0);
	$dateReservationStartTime     = htmlentities((isset($_REQUEST["reservationStartTime"])) ? $_REQUEST["reservationStartTime"] : 0);
	$dateReservationStartDateTime = $dateReservationStartDate + $dateReservationStartTime;

	$intRepeatEndOnQuantity = htmlentities((isset($_REQUEST["repeatEndOnQuantity"])) ? $_REQUEST["repeatEndOnQuantity"] : 0);
	$intRepeatInterval      = htmlentities((isset($_REQUEST["repeatInterval"])) ? $_REQUEST["repeatInterval"] : 0);
	$intReservationGroupID  = htmlentities((isset($_REQUEST["reservationGroupID"])) ? $_REQUEST["reservationGroupID"] : 0);

	# TODO must derive these lists
	$intSubgroupID = htmlentities((isset($_REQUEST["xxxxx"])) ? $_REQUEST["xxxxx"] : 0);
	$intItemID     = htmlentities((isset($_REQUEST["xxxxx"])) ? $_REQUEST["xxxxx"] : 0);

	# TODO must derive these lists
	$strRepeat_dom_10  = htmlentities((isset($_REQUEST["repeat_dom_10"])) ? util_quoteSmart($_REQUEST["repeat_dom_10"]) : 0);
	$strRepeat_dow_mon = htmlentities((isset($_REQUEST["repeat_dow_mon"])) ? util_quoteSmart($_REQUEST["repeat_dow_mon"]) : 0);

	$strRepeatEndType          = htmlentities((isset($_REQUEST["repeatEndType"])) ? util_quoteSmart($_REQUEST["repeatEndType"]) : 0);
	$strRepeatFrequencyType    = htmlentities((isset($_REQUEST["repeatFrequencyType"])) ? util_quoteSmart($_REQUEST["repeatFrequencyType"]) : 0);
	$strReservationSummaryText = htmlentities((isset($_REQUEST["reservationSummaryText"])) ? util_quoteSmart($_REQUEST["reservationSummaryText"]) : 0);


	#------------------------------------------------#
	# Set initial values
	#------------------------------------------------#


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
		# Summary: Insert 1 Schedule, X Reservations, 1 Time Block

		# Insert 1 Schedule
		//$sched = Schedule::getOneFromDb(['schedule_id' => 0], $DB);
		$sched = New Schedule(['DB' => $DB]);

		$sched->type          = $strReservationType;
		$sched->user_id       = $USER->user_id;
		$sched->frequncy_type = $strRepeatFrequencyType;
		$sched->summary       = $strReservationSummaryText;
		$sched->flag_all_day  = $bitAllDay;

		$sched->updateDb();

		$sched->refreshFromDb();

		# TODO GET primary key of last submitted schedule. use instead of $output attempt
		$output = Schedule::getOneFromDb(['schedule_id' => $this->schedule_id], $DB);

		# Insert X Reservations
		# Stuff all form elements starting with "subgroup-" and "item-" into array, sort by (item) value, then iterate and insert reservation records
		$reserveItems = [];
		foreach ($_REQUEST as $key => $val) {
			if ((substr($key, 0, 9) == 'subgroup-') || (substr($key, 0, 4) == 'item-')) {
				// Create array for storage. maybe unnecessary...
				$reserveItems['items'] = $val;

				$res = New Reservation(['DB' => $DB]);

				$res->eq_item     = $val;
				$res->schedule_id = $output->schedule_id;
				print_r($output->schedule_id);
				$res->updateDb();
			}
		}


		# Insert 1 Time Block
		# TODO: also requires previously inserted schedule_id

		# TODO: conflict checks, and below... weekly, monthly inserts

		# Output
//		$results['status'] = 'success';
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