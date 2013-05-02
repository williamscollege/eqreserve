<?php
	require_once('/classes/eq_group.class.php');
	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#
	$intID                      = htmlentities((isset($_POST["ajaxVal_ID"])) ? $_POST["ajaxVal_ID"] : 0 );
	$strName                    = htmlentities( (isset($_POST["ajaxVal_Name"])) ? util_quoteSmart($_POST["ajaxVal_Name"]) : 0 );
	$strDescription             = htmlentities( (isset($_POST["ajaxVal_Description"])) ? util_quoteSmart($_POST["ajaxVal_Description"]) : 0 );
	$strStartMinute             = htmlentities( (isset($_POST["ajaxVal_StartMinute"])) ? util_quoteSmart($_POST["ajaxVal_StartMinute"]) : 0 );
	$intMinDurationMinute       = htmlentities( (isset($_POST["ajaxVal_MinDurationMinute"])) ? $_POST["ajaxVal_MinDurationMinute"] : 0 );
	$intMaxDurationMinute       = htmlentities( (isset($_POST["ajaxVal_MaxDurationMinute"])) ? $_POST["ajaxVal_MaxDurationMinute"] : 0 );
	$intDurationIntervalMinutes = htmlentities( (isset($_POST["ajaxVal_DurationIntervalMinutes"])) ? $_POST["ajaxVal_DurationIntervalMinutes"] : 0 );


	#------------------------------------------------#
	# SQL: INSERT Item
	#------------------------------------------------#
	$eg = EqGroup::getOneFromDb(['eq_group_id' => $intID], $DB);

	if (!$eg->matchesDb) {
		// error: matching record DOES NOT already exist
		return false;
		exit;
	}
	$eg->name                   = $strName;
	$eg->descr                  = $strDescription;
	$eg->start_minute           = $strStartMinute;
	$eg->min_duration_minutes   = $intMinDurationMinute;
	$eg->max_duration_minutes   = $intMaxDurationMinute;
	$eg->duration_chunk_minutes = $intDurationIntervalMinutes;

	$eg->updateDB();

	# Output HTML (for ajax success/fail checker)
	echo "success!"

	/*
		Debugging:
		echo "<pre>"; print_r($_POST); echo "</pre>"; exit();
	*/
?>