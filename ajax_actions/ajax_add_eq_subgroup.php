<?php
	require_once('../classes/eq_group.class.php');
	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#
	$intID            = htmlentities((isset($_POST["ajaxVal_ID"])) ? $_POST["ajaxVal_ID"] : 0);
	$intOrder         = htmlentities((isset($_POST["ajaxVal_Order"])) ? $_POST["ajaxVal_Order"] : 0);
	$strName          = htmlentities((isset($_POST["ajaxVal_Name"])) ? util_quoteSmart($_POST["ajaxVal_Name"]) : 0);
	$strDescription   = htmlentities((isset($_POST["ajaxVal_Description"])) ? util_quoteSmart($_POST["ajaxVal_Description"]) : 0);
	$bitIsMultiSelect = htmlentities((isset($_POST["ajaxVal_MultiSelect"])) ? util_quoteSmart($_POST["ajaxVal_MultiSelect"]) : 0);


	#------------------------------------------------#
	# SQL: INSERT Item
	#------------------------------------------------#
	$esg = EqSubgroup::getOneFromDb(['name' => $strName], $DB);


	if ($esg->matchesDb) {
		// error: matching record already exists
		return FALSE;
		exit;
	}
	$esg->eq_group_id          = $intID;
	$esg->ordering             = $intOrder;
	$esg->name                 = $strName;
	$esg->descr                = $strDescription;
	$esg->flag_is_multi_select = $bitIsMultiSelect;

	$esg->updateDb();


	$output = EqSubgroup::getOneFromDb(['name' => $strName], $DB);


	# Output HTML

	# Subgroup Title
	echo "<ul id=\"ul-of-subgroup-" . $output->eq_subgroup_id . "\" class=\"unstyled\">\n";
	echo "<a href=\"#\" id=\"delete-subgroup-" . $output->eq_subgroup_id . "\" class=\"manager-action btn btn-mini btn-danger delete-subgroup-btn\" data-for-subgroup=\"" . $output->eq_subgroup_id . "\"><i class=\"icon-trash icon-white\"></i> </a> ";
	echo "<span data-subgroup-order=\"" . $output->ordering . "\"><strong>" . $output->name . ":</strong></span> " . $output->descr . "\n";
	# Button: Add an Item
	echo "<li class=\"manager-action\">"; # OMIT class="hide" as this is injected into the DOM
	echo "<span class=\"noItemsExist\"><em>No items exist.</em><br /></span>";
	echo "<a href=\"#modalAddItem\" data-subgroup-id=\"" . $output->eq_subgroup_id . "\" data-is-multiselect=\"" . $bitIsMultiSelect . "\" data-subgroup-name=\"" . $output->name . "\" data-toggle=\"modal\" class=\"btn btn-success btn-mini ajaxActionItem\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
	echo "</li>";
	echo "</ul>";


	/*
		Debugging:
		echo "<pre>"; print_r($_POST); echo "</pre>"; exit();
	*/
?>