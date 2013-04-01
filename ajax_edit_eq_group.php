<?php
	require_once('/classes/eq_group.class.php');
	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#

	$intID = (isset($_POST["ajaxVal_GroupID"])) ? $_POST["ajaxVal_GroupID"] : 0;
	$strName = (isset($_POST["ajaxVal_GroupName"])) ? util_quoteSmart($_POST["ajaxVal_GroupName"]) : 0;
	$strDescription = (isset($_POST["ajaxVal_GroupDescription"])) ? util_quoteSmart($_POST["ajaxVal_GroupDescription"]) : 0;
	$strStartMinute = (isset($_POST["ajaxVal_StartMinute"])) ? util_quoteSmart($_POST["ajaxVal_StartMinute"]) : 0;
	$intMinDurationMinute = (isset($_POST["ajaxVal_MinDurationMinute"])) ? $_POST["ajaxVal_MinDurationMinute"] : 0;
	$intMaxDurationMinute = (isset($_POST["ajaxVal_MaxDurationMinute"])) ? $_POST["ajaxVal_MaxDurationMinute"] : 0;
	$intDurationIntervalMinutes = (isset($_POST["ajaxVal_DurationIntervalMinutes"])) ? $_POST["ajaxVal_DurationIntervalMinutes"] : 0;

//	if ($intID == 0 || $strName == 0 || $strDescription == 0){
//		echo "failed at conditional check for ZERO";
////		echo $intID . "--" . $strName . "--" . $strDescription;
//		//util_redirectToAppHome('failure',50);
//	}

	#------------------------------------------------#
	# SQL: INSERT Item
	#------------------------------------------------#
	$eg = EqGroup::getOneFromDb(['eq_group_id'=>$intID], $DB);

	if (!$eg->matchesDb) {
		// handle here case where id does not exist
		echo "failed at MATCHESDB check";
//		util_redirectToAppHome('failure',51);
		exit;
	}
	$eg->name = $strName;
	$eg->descr = $strDescription;
	$eg->start_minute = $strStartMinute;
	$eg->min_duration_minutes = $intMinDurationMinute;
	$eg->max_duration_minutes = $intMaxDurationMinute;
	$eg->duration_chunk_minutes = $intDurationIntervalMinutes;

	$eg->updateDB();

//	echo "<pre>";
//	print_r($eg);
//	echo "</pre><hr/>";
//
//	$output = EqGroup::getOneFromDb(['eq_group_id'=>$intID], $DB);
//	echo "<pre>";
//	print_r($output);
//	echo "</pre>";

	# output html for ajax success/fail checker
	echo "success!"

	/*
	Debugging:
		echo $resultsStudentsUsingGlow;
		echo "<pre>" . print_r($_POST) . "</pre>";
		print_r($_REQUEST);
		exit();
	 */
?>