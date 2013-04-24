<?php
	require_once('/classes/eq_group.class.php');
	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#
	$strName        = (isset($_POST["ajaxVal_Name"])) ? util_quoteSmart($_POST["ajaxVal_Name"]) : 0;
	$strDescription = (isset($_POST["ajaxVal_Description"])) ? util_quoteSmart($_POST["ajaxVal_Description"]) : 0;

	#------------------------------------------------#
	# SQL: INSERT Item
	#------------------------------------------------#
	$eg = EqGroup::getOneFromDb(['name' => $strName, 'descr' => $strDescription], $DB);
	//	echo "<pre>";
	//	print_r($eg);
	//	echo "</pre>";

	if ($eg->matchesDb) {
		// handle here case where group already exists
		util_redirectToAppHome('failure', 61);
	}
	$eg->name  = $strName;
	$eg->descr = $strDescription;
	$eg->updateDb();
	//	echo "<pre>";
	//	print_r($eg);
	//	echo "</pre><hr/>";


	$output = EqGroup::getOneFromDb(['name' => $strName], $DB);
	//	echo "<pre>";
	//	print_r($test);
	//	echo "</pre>";

	# Output HTML
	echo "<li><a href=\"equipment_group.php?eid=" . $output->eq_group_id . "\" title=\"\">" . $output->name . "</a>: " . $output->descr . "</li>";



	/*
	Debugging:
		echo "<pre>" . print_r($_POST) . "</pre>";
		print_r($_REQUEST);
		exit();
	 */
?>