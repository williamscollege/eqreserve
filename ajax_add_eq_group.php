<?php
	echo 'about to process ajax php page...';



	exit;
	#------------------------------------------------#
	# Security: Require authentication and authorization
	include_once "../include/protected.php";
	#------------------------------------------------#

	# Connection String
	include_once "../include/connDB.php";
	# Public Functions
	include_once "../include/sanitation.php";


	#------------------------------------------------#
	# Forms Collections: AJAX posts and requests
	#------------------------------------------------#
	$strAddItem = (isset($_POST["ajaxVal"])) ? quote_smart($_POST["ajaxVal"]) : 0;


	if ($strAddItem !== 0 && $strAddItem != "" && $strAddItem != "''") {
		#------------------------------------------------#
		# SQL: INSERT Item
		# jQuery Plugin: FancyBox
		# AJAX: Add Item (BirthLocation)
		# Follow up: SELECT list from SQL; new addition is "selected"
		# Form name: frmAjaxAddBirthLocation
		# Input name: AjaxAddBirthLocation
		# Input type: string
		#------------------------------------------------#
		$queryAddBirthLocation = "
			INSERT INTO
				BirthLocation
			(
				Location
			)
			VALUES (
				$strAddItem
			);
		";

		$resultsAddBirthLocation = mysqli_query($connString, $queryAddBirthLocation) or
			die(mysqli_error($connString));

		// Get the ID generated in the last query
		$intRowID = mysqli_insert_id($connString);
	}

	# Explicitly declare this in event it is not declared in above conditional statement
	if (!isset($intRowID)) {
		$intRowID = 0;
	}

	#------------------------------------------------#
	# SQL: Fetch BirthLocation options
	#------------------------------------------------#
	$queryBirthLocation = "
			SELECT
				BirthLocationID
				,Location
			FROM
				BirthLocation
			ORDER BY Location ASC;
		";
	$resultsBirthLocation = mysqli_query($connString, $queryBirthLocation) or
		die(mysqli_error($connString));


	/*
	Debugging:
		echo $resultsStudentsUsingGlow;
		echo "<pre>" . print_r($_POST) . "</pre>";
		print_r($_REQUEST);
		exit();
	 */
?>

<select id="BirthLocationID" name="BirthLocationID" class="">
	<option value="0">--Select--</option>
	<?php
	while ($rowBG = mysqli_fetch_array($resultsBirthLocation)) {
		if ($intRowID == $rowBG['BirthLocationID']) {
			$strSelected = "selected='selected'";
		} else {
			$strSelected = "";
		}
		echo "<option value='$rowBG[BirthLocationID]' $strSelected>$rowBG[Location]</option>";
	}
	?>
</select>