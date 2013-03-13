<?php
	$pageTitle = 'Home';
	require_once('head.php');
	require_once('/classes/eq_group.class.php');


	if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
		// SECTION: authenticated

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
		# instantiate the equipment groups and roles for this user
		$UserEqGroups = new EqGroup([$USER, 'DB' => $DB]);
		$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);
		echo "<h3>Equipment Groups</h3>";
		echo "<ul>";
		if (count($UserEqGroups) > 0) {
			for ($i = 0, $size = count($UserEqGroups); $i < $size; ++$i) {
				echo "<li><a href=\"equipment_group.php?eid=" . $UserEqGroups[$i]['eq_group_id'] . "\" title=\"\">" . $UserEqGroups[$i]['name'] . "</a> [description: " . $UserEqGroups[$i]['descr'] . "]</li>";
			}
		} else {
			echo "<li>You do not belong to any equipment groups.</li>";
		}
		if ($USER->flag_is_system_admin == TRUE) {
			// system admin may add new eq_groups
			?>
		<form>
            <button type="button" id="btnDisplayNewEqGroup" class="btn btn-primary">Create a new equipment group
            </button>

            <div id="eqGroupFields" class="displayNone">
                <fieldset title="">
                    <legend>Create a new equipment group</legend>
                    <label>Name</label>
					<input type="text" id="eqGroupName" class="" value="" placeholder="Name of group" /><br />
                    <label>Description</label>
					<textarea id="eqGroupDescription" class="" placeholder="Description of group"></textarea><br />
                    <button type="button" id="btnSubmitNewEqGroup" class="btn btn-success">Save Group</button>
                    <button type="button" id="btnCancelNewEqGroup" class="btn">Cancel</button>
                    <br />TODO: AJAX submit; then update list of EqGroups above to include this group
                </fieldset>
            </div>
        </form>
		<?php
		}
		echo "</ul>";

	} else {
		// SECTION: not yet authenticated, wants to log in
		?>
    <div class="hero-unit">
        <h1>Equipment Reservations:</h1>

        <br />

        <p>This is our system for scheduling equipment reservations.</p>

        <p>To sign in, please use your Williams username and password.</p>

    </div>
	<?php
	}

	require_once('foot.php');
?>