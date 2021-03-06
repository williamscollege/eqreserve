<?php
	require_once('../classes/eq_group.class.php');
	require_once('../classes/eq_item.class.php');

	require_once('head_ajax.php');


	#------------------------------------------------#
	# Fetch AJAX values
	#------------------------------------------------#
	$strAction        = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);
	$intDeleteID      = htmlentities((isset($_REQUEST["ajaxVal_Delete_ID"])) ? $_REQUEST["ajaxVal_Delete_ID"] : 0);
	$intSubgroupID    = htmlentities((isset($_REQUEST["ajaxVal_SubgroupID"])) ? $_REQUEST["ajaxVal_SubgroupID"] : 0);
	$intSubgroupName  = htmlentities((isset($_REQUEST["ajaxVal_SubgroupName"])) ? $_REQUEST["ajaxVal_SubgroupName"] : 0);
	$intItemID        = htmlentities((isset($_REQUEST["ajaxVal_ItemID"])) ? $_REQUEST["ajaxVal_ItemID"] : 0);
	$intOrder         = htmlentities((isset($_REQUEST["ajaxVal_Order"])) ? $_REQUEST["ajaxVal_Order"] : 0);
	$strName          = htmlentities((isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : 0);
	$strDescription   = htmlentities((isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : 0);
        $strReference     = htmlentities((isset($_REQUEST["ajaxVal_Reference"])) ? util_quoteSmart($_REQUEST["ajaxVal_Reference"]) : 0);
	$bitIsMultiSelect = htmlentities((isset($_REQUEST["ajaxVal_MultiSelect"])) ? util_quoteSmart($_REQUEST["ajaxVal_MultiSelect"]) : 0);
        $strImageFileName = htmlentities((isset($_REQUEST["ajaxVal_ImageFileName"])) ? util_quoteSmart($_REQUEST["ajaxVal_ImageFileName"]) : 0);



	$strAction = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);

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
	if ($strAction == 'add-item') {
		$ei = EqItem::getOneFromDb(['eq_subgroup_id' => $intSubgroupID, 'name' => $strName], $DB);

		if ($ei->matchesDb) {
			// error: matching record already exists
            $results['message']       = 'A record with that same name already exists in database.';
            echo json_encode($results);
			exit;
		}
		$ei->eq_subgroup_id = $intSubgroupID;
		$ei->ordering       = $intOrder;
		$ei->name           = $strName;
		$ei->descr          = $strDescription;
        $ei->reference_link = $strReference;
        $ei->image_file_name = $strImageFileName;
        $ei->flag_image_to_be_uploaded = ($strImageFileName != '');

		$ei->updateDb();

		# Fetch the Item from this specific subgroup
		$output = EqItem::getOneFromDb(['eq_subgroup_id' => $intSubgroupID, 'name' => $strName], $DB);

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'add-item';
        $results['for_item_id'] = $output->eq_item_id;
		$results['html_output']  = '';

		# Omit class="hide" as this is injected into the DOM
		$results['html_output'] .= "<li id=\"list-of-item-" . $output->eq_item_id . "\"  class=\"item-in-a-subgroup\" data-for-item-order=\"" . $output->ordering . "\">";
		$results['html_output'] .= "<label class=\"\" for=\"item-" . $output->eq_item_id . "\">";
		$results['html_output'] .= "<a id=\"btn-edit-item-id-" . $output->eq_item_id . "\" href=\"#modalItem\" data-toggle=\"modal\" data-for-subgroup-name=\"" . $intSubgroupName . "\" data-for-item-id=\"" . $output->eq_item_id . "\" data-for-item-name=\"" . $output->name . "\" data-for-item-descr=\"" . $output->descr . "\" data-for-item-ref=\"" . $output->reference_link . "\" class=\"manager-action btn btn-mini btn-primary eq-edit-item\" title=\"Edit\"><i class=\"icon-pencil icon-white\"></i> </a> ";
		$results['html_output'] .= "<a id=\"delete-item-" . $output->eq_item_id . "\" class=\"manager-action btn btn-mini btn-danger eq-delete-item\"  data-for-item-name=\"" . $output->name . "\" data-for-item-id=\"" . $output->eq_item_id . "\"><i class=\"icon-trash icon-white\"></i> </a> ";
		if ($bitIsMultiSelect == 0) {
			# radio: single select
			$results['html_output'] .= "<input type=\"radio\" id=\"item-" . $output->eq_item_id . "\" name=\"subgroup-" . $output->eq_subgroup_id . "\" value=\"" . $output->eq_item_id . "\" class=\"reservationForm hide\" /> ";
		}
		elseif ($bitIsMultiSelect == 1) {
			# checkbox: multiple select
			$results['html_output'] .= "<input type=\"checkbox\" id=\"item-" . $output->eq_item_id . "\" name=\"subgroup-" . $output->eq_subgroup_id . "-" . $output->eq_item_id . "\" value=\"" . $output->eq_item_id . "\" class=\"reservationForm hide\" /> ";
		}
		$results['html_output'] .= "<span id=\"itemid-" . $output->eq_item_id . "\"><strong>" . $output->name . ": </strong>" . $output->descr . "(". $output->reference_link .")</span>\n";
        $results['html_output'] .= "<span id=\"itemImageSpanFor" . $output->eq_item_id . "\"><i>[no image available]</i></span>";
		$results['html_output'] .= "</label>";
		$results['html_output'] .= "</li>";
	}
	//###############################################################
	elseif ($strAction == 'edit-item') {
		$ei = EqItem::getOneFromDb(['eq_item_id' => $intItemID], $DB);

		if (!$ei->matchesDb) {
			// error: no matching record found
            $results['message']       = 'Could not find that item for editing : '.$intItemID;
			echo json_encode($results);
			exit;
		}

		#set field values for item
		$ei->eq_subgroup_id = $intSubgroupID;
		$ei->eq_subgroup = EqSubgroup::getOneFromDb(['eq_subgroup_id' => $ei->subgroup_id, 'flag_delete' => FALSE], $DB);
		$ei->name  = $strName;
		$ei->descr = $strDescription;
        $ei->reference_link = $strReference;

		#set field values for subgroups

        $results['has_no_image']  = 0;
        if (! $strImageFileName || ($strImageFileName == 'none')) {
            $ei->image_file_name = '';
            $ei->flag_image_to_be_uploaded = false;
            $results['has_no_image']  = 1;
        }
        if ($strImageFileName && ($ei->image_file_name != $strImageFileName) && ($strImageFileName != 'nochange')) {
            $ei->image_file_name = $strImageFileName;
            $ei->flag_image_to_be_uploaded = true;
        }

        $ei->updateDb();

		# Output
		$results['status']       = 'success';
		$results['which_action'] = 'edit-item';
        $results['for_item_id'] = $intItemID;
		$results['html_output']  = '';
	}
	//###############################################################
	elseif ($strAction == 'delete-item') {
		$ei = EqItem::getOneFromDb(['eq_item_id' => $intDeleteID], $DB);

		if (!$ei->matchesDb) {
			// error: no matching record found
            $results['message']       = 'Could not find that item for deleting : '.$intItemID;
			echo json_encode($results);
			exit;
		}

		$ei->flag_delete = TRUE;
		$ei->updateDb();

		# Output
		if ($ei->matchesDb) {
			$results['status'] = 'success';
		}
	}
	//###############################################################
    else {
        $results['message']       = 'unknown action : '.$strAction;
    }

	#------------------------------------------------#
	# Debugging output
	#------------------------------------------------#
	//	echo "<pre>"; print_r($_REQUEST); echo "</pre>"; exit();


	#------------------------------------------------#
	# Return JSON array
	#------------------------------------------------#
	echo json_encode($results);
	exit;

?>