<?php
	$pageTitle = 'Home';
	require_once('head.php');
	require_once('/classes/eq_group.class.php');


	if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {

		?>
    <script type="text/javascript">
        $(document).ready(function () {
            // default conditions

            // ***************************
            // Listeners
            // ***************************
            // Listener: Admin Button Clicks
            $("#btnDisplayNewEqGroup").click(function () {
                $("#btnDisplayNewEqGroup").addClass('displayNone');
                $("#eqGroupFields").removeClass('displayNone');
            });
            $("#btnCancelNewEqGroup").click(function () {
                $("#btnDisplayNewEqGroup").removeClass('displayNone');
                $("#eqGroupFields").addClass('displayNone');
            });
        });
    </script>
	<?php


		echo "<hr />";
		echo "<h3>My Equipment Groups</h3>";
		# instantiate the equipment groups and roles for this user
		$UserEqGroups = new EqGroup([$USER, 'DB' => $DB]);
		echo "<ul>";
		$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);
		if (count($UserEqGroups) > 0) {
			for ($i = 0, $size = count($UserEqGroups); $i < $size; ++$i) {
				echo "<li><a href=\"equipment_group.php?eid=" . $UserEqGroups[$i]['eq_group_id'] . "\" title=\"\">" . $UserEqGroups[$i]['name'] . "</a> [description: " . $UserEqGroups[$i]['descr'] . "]</li>";
			}
		} else {
			echo "<li>You do not belong to any equipment groups.</li>";
		}
		echo "</ul>";


		# TODO: SYS ADMIN Section needs security added
		echo "<hr />";
		echo "<h3>Admin: All Equipment Groups</h3>";
		echo "<ul>";
		# instantiate the equipment groups for the system administrator
		$AdminEqGroups = EqGroup::getAllFromDb(['flag_delete' => 0], $DB); //EqGroup::getAllEqGroups($DB);
		if (count($AdminEqGroups) > 0) {
			for ($i = 0, $size = count($AdminEqGroups); $i < $size; ++$i) {
				echo "<li><a href=\"equipment_group.php?eid=" . $AdminEqGroups[$i]->eq_group_id . "\" title=\"\">" . $AdminEqGroups[$i]->name . "</a> [description: " . $AdminEqGroups[$i]->descr . "]</li>";
			}
		} else {
			echo "<li>No equipment groups exist.</li>";
		}
		?>
    <div class="admin_user">
        <form>
            <input id="btnDisplayNewEqGroup" type="button" value="Create a new equipment group" />

            <div id="eqGroupFields" class="displayNone">
                <fieldset title="">
                    <legend>Create a new equipment group</legend>
                    Name of group: <input type="text" id="" class="" value="" /><br />
                    Description of group: <textarea id="" class=""></textarea><br />
                    <input type="button" id="btnSubmitNewEqGroup" class="" value="Add Group" />
                    <input type="button" id="btnCancelNewEqGroup" class="" value="Cancel" />
                    <br />NOTE: AJAX submit; then update list of EqGroups above to include this group
                    <br />NOTE: cancel will hide fields, and show the initial button
                </fieldset>
            </div>
        </form>
    </div>
	<?php
		echo "</ul>";

		# DEVINFO
		echo "<hr />";
		echo "<div class=\"DEVINFO\">";
		echo "<h3>User Info:</h3>";
		echo "username: " . $_SESSION['userdata']['username'] . "<br />";
		echo "email: " . $_SESSION['userdata']['email'] . "<br />";
		echo "firstname: " . $_SESSION['userdata']['firstname'] . "<br />";
		echo "lastname: " . $_SESSION['userdata']['lastname'] . "<br />";
		echo "sortname: " . $_SESSION['userdata']['sortname'] . "<br />";
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