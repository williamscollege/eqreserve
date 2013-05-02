<?php
	require_once('/classes/eq_group.class.php');
	require_once('/head_ajax.php');


	$results = [
		'status'=> 'success'
	];

	if (! $schedule) {
		echo json_encode($results);
		exit;
	}
	exit;

//	#------------------------------------------------#
//	# Forms Collections: AJAX posts and requests
//	#------------------------------------------------#
//	$strName        = htmlentities((isset($_POST["ajaxVal_Name"])) ? util_quoteSmart($_POST["ajaxVal_Name"]) : 0);
//	$strDescription = htmlentities((isset($_POST["ajaxVal_Description"])) ? util_quoteSmart($_POST["ajaxVal_Description"]) : 0);
//
//
//	#------------------------------------------------#
//	# SQL: INSERT Item
//	#------------------------------------------------#
//	$eg = EqGroup::getOneFromDb(['name' => $strName], $DB);
//
//
//	if ($eg->matchesDb) {
//		// error: matching record already exists
//		return FALSE;
//		exit;
//	}
//	$eg->name  = $strName;
//	$eg->descr = $strDescription;
//	$eg->updateDb();
//
//
//	$output = EqGroup::getOneFromDb(['name' => $strName], $DB);
//
//
//	# Output HTML
//	echo "<li><a href=\"equipment_group.php?eid=" . $output->eq_group_id . "\" title=\"\">" . $output->name . "</a>: " . $output->descr . "</li>";


	/*
		Debugging:
		echo "<pre>"; print_r($_POST); echo "</pre>"; exit();
	*/
?>