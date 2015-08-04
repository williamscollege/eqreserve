<hr>

<footer>
	<!-- Link to trigger modal -->
	<p class="pull-right"><a href="#modalHelp" data-toggle="modal"><i class="icon-question-sign"></i> Need Help</a>? Williams College, <?php echo date('Y'); ?>
	</p>

	<div id="modalHelp" class="modal hide" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalHelpLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <?php
//            util_prePrintR(basename($_SERVER['PHP_SELF']));
            if(basename($_SERVER['PHP_SELF']) == 'index.php') {
                if($IS_AUTHENTICATED){
                    echo '<h3 id="modalHelpLabel">Home Page FAQ</h3></div><div class="modal-body">';
                    echo '<ol>';
                    echo '<li><p>Your home page shows the list of groups that you have access to.</p></li>';
                    echo '<li><p>You can find your account information by clicking on your username in the top right-hand corner.</p></li>';
                    echo '<li><p>Click on a group name for more information about that group.</p></li>';
                    echo '<li><p>If you are a manager, indicated by "(manager)," you will be able to edit certain aspects of that group.</p></li></ol>';
                }else{
                    echo '<h3 id="modalHelpLabel">Help FAQ</h3></div><div class="modal-body">';
                    echo '<ol>';
                    echo '<li><p>To sign in, please use your Williams username and password.</p></li>';
                    echo '<li><p>Users must initially log in to the Equipment Reservation system one time, in order for this system to be "aware" of them and send them notifications, etc.</p></li></ol>';
                }
             }else if(basename($_SERVER['PHP_SELF']) == "equipment_group.php"){
                echo '<h3 id="modalHelpLabel">Equipment Group FAQ</h3></div><div class="modal-body">';
                echo '<ol>';
                echo '<li><p>The Equipment Group Page contains general information about the group, including items and reservations.</p></li>';
                if($is_group_manager){
                    echo '<li><p>As a manager of this group, you can edit the equipment group by clicking edit equipment group.</p></li>';
                    echo '<li><p>Manager function include editing group information, changing reservation rules, deleting, editing, or inputting subgroups and items, and deleting existing reservations</p></li>';
                    echo '<li><p>For reservation restrictions, either choose from the default times or input your own in the box.</p></li>';
                }
                echo '<li><p>Reservation restrictions tell a user the minimum and maximum time that an item can be reserved for, on what minutes of the hour the reservation can start on, and the intervals for which it can be reserved.</p></li>';
                echo '<li><p>To reserve an item, click "Reserve Equipment." Choose at least one item to reserve, specify your date, time, duration, and any repeats, and then press submit.</p></li>';
                echo '<li><p>Schedule conflicts or failure to follow the reservation restrictions will result in a reservation error.</p></li>';
                echo '<li><p>Existing reservations for the items can be seen in a list, monthly, and daily view.</p></li>';
                echo '<li><p>For more information about your reservation, click on the date of your reservation while in list view.</p></li>';
                echo '<li><p>To see the reservations in a daily view, click on a day in the calendar.</p></li>';
                echo '<li><p>If your reservation does not show up immediately, please refresh the page.</p></li>';
                echo '</ol>';
            }else if(basename($_SERVER['PHP_SELF']) == 'schedule.php') {
                echo '<h3 id="modalHelpLabel">Schedule of Reservations FAQ</h3></div><div class="modal-body">';
                echo '<ol>';
                echo '<li><p>The Schedule of Reservations Page gives a detailed look at your individual item reservation</p></li>';
                echo '<li><p>If you are a manager of the Equipment Group you have reserved under, you can change the reservation to be a regular or a management schedule. This will show up on the Equipment Group Page as "MANAGEMENT."</p></li>';
                echo '<li><p>Click edit to delete a part or all of your reservation.</p></li></ol>';
            }else if($USER->flag_is_system_admin && basename($_SERVER['PHP_SELF']) == 'admin_manage_users.php') {
                echo '<h3 id="modalHelpLabel">Manage Users FAQ</h3></div><div class="modal-body">';
                echo '<ol>';
                echo '<li><p>This page is only available to administrators.</p></li>';
                echo '<li><p>The Manage Users Page contains a list of all users in the system.</p></li></ol>';
            }else if($USER->flag_is_system_admin && basename($_SERVER['PHP_SELF']) == 'admin_manage_groups_courses.php') {
                echo '<h3 id="modalHelpLabel">Manage Groups/Courses FAQ</h3></div><div class="modal-body">';
                echo '<ol>';
                echo '<li><p>This page is only available to administrators.</p></li>';
                echo '<li><p>The Manage Groups/Courses Page contains a list of all groups/courses in the system.</p></li></ol>';
            }else if(basename($_SERVER['PHP_SELF']) == 'account_management.php'){
                echo '<h3 id="modalHelpLabel">Account Management FAQ</h3></div><div class="modal-body">';
                echo '<ol>';
                echo '<li><p>This page holds a detailed look at your account.</p></li>';
                echo '<li><p>You can find groups/courses that you are a member of as well as all reservations that you have made.</p></li>';
                echo '<li><p>Turn on reminders for Equipment Groups that you have access to by checking the appropriate box.</p></li>';
                echo '<li><p>If you are a manager of an Equipment Group, you can also turn on/off alerts for any reservations regarding that group.</p></li></ol>';
            }
            ?>
			<p>&nbsp;</p>
			<p><i class="icon-question-sign"></i> More questions?</p>
				<?php
				if (isset($managersList)) {
					# show list of managers for this group
					echo "<p>Please contact: " . $managersList . "</p>";
				}
				else {
					# show default support address
					echo "<p>Please contact: <a href=\"mailto:itech@" . INSTITUTION_DOMAIN . "?subject=EqReserve_Help_Request\"><i class=\"icon-envelope\"></i> itech@williams.edu</a></p>";
				}
				?>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		</div>
	</div>
</footer>

</div> <!-- /container -->

</body>
</html>