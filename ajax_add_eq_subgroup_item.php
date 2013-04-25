<?php
	require_once('/classes/eq_group.class.php');
	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#
	$intID            = (isset($_POST["ajaxVal_ID"])) ? $_POST["ajaxVal_ID"] : 0;
	$intOrder         = (isset($_POST["ajaxVal_Order"])) ? $_POST["ajaxVal_Order"] : 0;
	$strName          = (isset($_POST["ajaxVal_Name"])) ? util_quoteSmart($_POST["ajaxVal_Name"]) : 0;
	$strDescription   = (isset($_POST["ajaxVal_Description"])) ? util_quoteSmart($_POST["ajaxVal_Description"]) : 0;
	$bitIsMultiSelect = (isset($_POST["ajaxVal_MultiSelect"])) ? util_quoteSmart($_POST["ajaxVal_MultiSelect"]) : 0;

	#------------------------------------------------#
	# SQL: INSERT Item
	#------------------------------------------------#
	$ei = EqItem::getOneFromDb(['eq_subgroup_id' => $intID, 'name' => $strName, 'descr' => $strDescription], $DB);
	//	echo "<pre>";
	//	print_r($ei);
	//	echo "</pre>";

	if ($ei->matchesDb) {
		// handle here case where group already exists
		util_redirectToAppHome('failure', 61);
	}
	$ei->eq_subgroup_id = $intID;
	$ei->ordering       = $intOrder;
	$ei->name           = $strName;
	$ei->descr          = $strDescription;

	$ei->updateDb();
	//	echo "<pre>";
	//	print_r($ei);
	//	echo "</pre><hr/>";

	# Fetch the Item from this specific subgroup
	$output = EqItem::getOneFromDb(['eq_subgroup_id' => $intID, 'name' => $strName], $DB);
	//	echo "<pre>";
	//	print_r($test);
	//	echo "</pre>";

	# Output HTML

	# Item Title
	echo "<li data-item-order=\"" . $output->ordering . "\">";
	echo "<div class=\"span1\">&nbsp;</div>";
	echo "<label class=\"\" for=\"" . $output->eq_item_id . "\">";
	#conditional logic needed here to define: radio OR checkbox
	if ($bitIsMultiSelect == 0) {
		# radio: single select
		echo "<input type=\"radio\" id=\"item" . $output->eq_item_id . "\" name=\"subgroup" . $output->eq_subgroup_id . "\" class=\"reservationForm hide\" /> ";
	}
	elseif ($bitIsMultiSelect == 1) {
		# checkbox: multiple select
		echo "<input type=\"checkbox\" id=\"item" . $output->eq_item_id . "\" name=\"subgroup" . $output->eq_subgroup_id . "\" class=\"reservationForm hide\" /> ";
	}
	echo "<strong>" . $output->name . "</strong>: " . $output->descr . " </label>";
	echo "<!--Placeholder: Save For Later Use-->";
	echo "</li>";


	/*
	Debugging:
		echo "<pre>"; print_r($_POST); echo "</pre>";
		print_r($_REQUEST);
		exit();
	 */
?>