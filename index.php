<?php
$pageTitle = 'Home';
require_once('head.php');
require_once('/classes/eq_group.class.php');


if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {

/*
	# Equipment Groups
	echo "<h3>(User) Equipment Groups</h3>";

	# instantiate the equipment groups and roles for this user
	$UserEqGroups = new EqGroup();
	echo "<ul>";
	$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($_SESSION['userdata']['username']);
	if (count($UserEqGroups)>0) {
		for ($i = 0, $size = count($UserEqGroups); $i < $size; ++$i) {
			echo "<li><a href=\"equipment_group.php?eid=" . $UserEqGroups[$i]['eq_group_id'] . "\" title=\"\">" . $UserEqGroups[$i]['name'] . "</a> [description: " . $UserEqGroups[$i]['descr'] . "]</li>";
		}
	} else {
		echo "<li>You do not belong to any equipment groups.</li>";
	}
	echo "</ul>";
*/
	
	echo "<h3>(Sys Admin) Equipment Groups</h3>";
	echo "<ul>";

	# instantiate the equipment groups for the system administrator
	$AdminEqGroups = EqGroup::loadAllFromDb(['flag_delete'=>0],$DB); //EqGroup::getAllEqGroups($DB);
	if (count($AdminEqGroups)>0) {
		for ($i = 0, $size = count($AdminEqGroups); $i < $size; ++$i) {
			echo "<li><a href=\"equipment_group.php?eid=" . $AdminEqGroups[$i]->eq_group_id . "\" title=\"\">" . $AdminEqGroups[$i]->name . "</a> [description: " . $AdminEqGroups[$i]->descr . "]</li>";
		}
	} else {
		echo "<li>There are currently no equipment groups.</li>";
	}
	echo "</ul>";

	# DEVINFO
	echo "<div class=\"DEVINFO\">";
	echo "<h3>User Info:</h3>";
	echo "username: " . $_SESSION['userdata']['username'] . "<br />";
	echo "email: " . $_SESSION['userdata']['email'] . "<br />";
	echo "fullname: " . $_SESSION['userdata']['fullname'] . "<br />";
	echo "firstname: " . $_SESSION['userdata']['firstname'] . "<br />";
	echo "lastname: " . $_SESSION['userdata']['lastname'] . "<br />";
	echo "sortname: " . $_SESSION['userdata']['sortname'] . "<br />";
	echo "position: " . $_SESSION['userdata']['position'] . "<br />";
	echo "institutional groups:<br />";
	echo "<ul>";
	for ($i = 0, $size = count($_SESSION['userdata']['inst_groups']); $i < $size; ++$i) {
		echo "<li>" . $_SESSION['userdata']['inst_groups'][$i] . "</li>";
	}
	echo "</ul>";
	echo "<br />";
	echo "</div>";
}


require_once('foot.php');
?>