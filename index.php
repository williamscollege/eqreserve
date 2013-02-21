<?php
$pageTitle = 'Home';
require_once('head.php');
require_once('/classes/eq_group.class.php');


if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
/*
	# Equipment Groups
	echo "<h3>Equipment Groups</h3>";
	echo "<ul>";

	# instantiate the equipment groups and roles for this user
	# ??? Will this be instantiated upon every visit to this page? wasteful! need to fix.
	$EqGroups = new EqGroup();
	if ($EqGroups->getEqGroups($_SESSION['userdata']['username'])) {
		for ($i = 0, $size = count($EqGroups->eq_group_id); $i < $size; ++$i) {
			echo "<li><a href=\"equipment_group.php?eid=" . $EqGroups[$i]['eq_group_id'] . "\" title=\"\">" . $EqGroups[$i]['name'] . "</a> [description: " . $EqGroups[$i]['descr'] . "]</li>";
		}
	} else {
		echo "<li>You do not belong to any equipment groups.</li>";
	}
	echo "</ul>";
*/
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