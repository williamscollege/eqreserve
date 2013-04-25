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
	$esg = EqSubgroup::getOneFromDb(['name' => $strName, 'descr' => $strDescription], $DB);
	//	echo "<pre>";
	//	print_r($esg);
	//	echo "</pre>";

	if ($esg->matchesDb) {
		// handle here case where group already exists
		util_redirectToAppHome('failure', 61);
	}
	$esg->eq_group_id          = $intID;
	$esg->ordering             = $intOrder;
	$esg->name                 = $strName;
	$esg->descr                = $strDescription;
	$esg->flag_is_multi_select = $bitIsMultiSelect;

	$esg->updateDb();
	//	echo "<pre>";
	//	print_r($esg);
	//	echo "</pre><hr/>";

	$output = EqSubgroup::getOneFromDb(['name' => $strName], $DB);
	//	echo "<pre>";
	//	print_r($test);
	//	echo "</pre>";

	# Output HTML

	# Subgroup Title
	echo "<ul id=\"displaySubgroup" . $output->eq_subgroup_id . "Items\" class=\"unstyled\">\n";
	echo "<span data-subgroup-order=\"" . $output->ordering . "\"><strong>" . $output->name . ":</strong></span> " . $output->descr . "\n";
	echo "<li data-item-order=\"0\"><div class=\"span1\">&nbsp;</div><em>No items exist.</em></li>";
	# Button: Add an Item
	echo "<li class=\"manager-action\">"; # OMIT class="hide" as this is injected into the DOM
	echo "<div class=\"span1\"></div>";
	echo "<a href=\"#modalAddItem\" data-subgroup-id=\"" . $output->eq_subgroup_id . "\" data-is-multiselect=\"" . $bitIsMultiSelect . "\" data-subgroup-name=\"" . $output->name . "\" data-toggle=\"modal\" class=\"btn btn-primary btn-mini ajaxActionItem\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
	echo "</li>";
	echo "</ul>";


	/*
	Debugging:
		echo "<pre>"; print_r($_POST); echo "</pre>";
		print_r($_REQUEST);
		exit();
	 */
?>