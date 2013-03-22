<?php
	require_once('/classes/eq_group.class.php');
	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#
	$strName = (isset($_POST["ajaxVal_GroupName"])) ? quote_smart($_POST["ajaxVal_GroupName"]) : 0;
	$strDescription = (isset($_POST["ajaxVal_GroupDescription"])) ? quote_smart($_POST["ajaxVal_GroupDescription"]) : 0;

	#------------------------------------------------#
	# SQL: INSERT Item
	#------------------------------------------------#
	//	$eg = new EqGroup(['name'=>$strName,'descr'=>$strDescription,'DB'=>$this->DB]);
	$eg = EqGroup::getOneFromDb(['name'=>$strName,'descr'=>$strDescription], $DB);
//	echo "<pre>";
//	print_r($eg);
//	echo "</pre>";

	if ($eg->matchesDb) {
		// handle here case where group already exists
		exit;
	}
	$eg->name = $strName;
	$eg->descr = $strDescription;
	$eg->updateDb();
//	echo "<pre>";
//	print_r($eg);
//	echo "</pre>";


	$output = EqGroup::getOneFromDb(['name'=>$strName], $DB);
//	echo "<pre>";
//	print_r($test);
//	echo "</pre>";

	# output html
	echo "<li><a href=\"equipment_group.php?eid=" . $output->eq_group_id . "\" title=\"\">" . $output->name . "</a>: " . $output->descr . "</li>";



	/*
	Debugging:
		echo $resultsStudentsUsingGlow;
		echo "<pre>" . print_r($_POST) . "</pre>";
		print_r($_REQUEST);
		exit();
	 */
?>
