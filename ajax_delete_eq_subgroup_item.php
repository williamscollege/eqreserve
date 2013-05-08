<?php
	require_once('/classes/eq_item.class.php');
	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#
	$strAction = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);
	$intID     = htmlentities((isset($_REQUEST["ajaxVal_ID"])) ? $_REQUEST["ajaxVal_ID"] : 0);


	#------------------------------------------------#
	# SQL: DELETE Item
	#------------------------------------------------#
	# set initial return value
	$results = [
		'status' => 'failure'
	];

	$ei = EqItem::getOneFromDb(['eq_item_id' => $intID], $DB);

	if (!$ei->matchesDb) {
		// error: matching record already exists
		echo json_encode($results);
		exit;
	}

	if ($strAction == 'deleteItem') {
		$ei->flag_delete = TRUE;
		$ei->updateDb();
		if ($ei->matchesDb) {
			$results['status'] = 'success';
		}
	}


	# Output JSON
	echo json_encode($results);


	/*
		Debugging:
		echo "<pre>"; print_r($_REQUEST); echo "</pre>"; exit();
	*/
?>