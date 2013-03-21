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
            $("#btnDisplayAddEqGroup").click(function () {
                $("#btnDisplayAddEqGroup").addClass('displayNone');
                $("#eqGroupFields").removeClass('displayNone');
            });
			$("#btnSubmitAddEqGroup").click(function(){
				var cachedForm = $(this).parents('form');	// store local reference to DOM of Form that sent request
                var url = cachedForm.attr('action');		// get url from the form element
                var formName = cachedForm.attr('name');		// get name from the form element
                var data1 = $('#' + formName + ' #eqGroupName').val();
                var data2 = $('#' + formName + ' #eqGroupDescription').val();

                alert('cachedForm=' + cachedForm);
                alert('url=' + url);
                alert('formName=' + formName);
                alert('data1=' + data1);
                alert('data2=' + data2);

				$.ajax({
                    type:'POST',
                    url:url,
                    data:{
                        ajaxVal_GroupName:data1,
                        ajaxVal_GroupDescription:data1
                        //ajaxDestination:destination    // optional param value is used when the select statement to be generated could be one of many
                    },
                    dataType:'html',
                    success:function (data) {
                        // alert if update failed
                        if (data) {
                            // update the element with new data from the ajax call
                            // alert(data); // debugging
                            //$('#' + destination).html(data);
							// refreshDB() Here?

                            // show message
//                            $('SPAN.notice').css('display', 'none'); // first, hide any and all pre-existing notices
//                            $('#' + dest).parent().children('A').addClass('displayNone'); // temporarily, hide the anchor element
//                            $('#' + dest).parent().children('SPAN.notice').addClass('miniSuccess').text('Updated').fadeIn('slow');
                        } else {
//                            $('SPAN.notice').css('display', 'none'); // first, hide any and all pre-existing notices
//                            $('#' + dest).parent().children('SPAN.notice').addClass('miniError').text('Requested action failed.').fadeIn('slow');
                        }
                        //$.fancybox.close(); // Hide the FancyBox
                    }
                });
			});
            $("#btnCancelAddEqGroup").click(function () {
                $("#btnDisplayAddEqGroup").removeClass('displayNone');
                $("#eqGroupFields").addClass('displayNone');
            });

        });
    </script>

	<?php
		echo "<hr />";
		# instantiate the equipment groups and roles for this user
		$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);

		echo "<h3>Equipment Groups</h3>";
		echo "<ul>";
		if (count($UserEqGroups) > 0) {
//			echo "<pre>LOOKATEG:"; print_r($UserEqGroups); echo "</pre><hr>";
			foreach($UserEqGroups as $ueg){
				echo "<li><a href=\"equipment_group.php?eid=" . $ueg->eq_group_id . "\" title=\"\">" . $ueg->name . "</a>: " . $ueg->descr ;
				if ($ueg->permission->role->priority == 1){
					echo " (You manage this group)";
				}
				echo "</li>";
			}
		} else {
			echo "<li>You do not belong to any equipment groups.</li>";
		}
		if ($USER->flag_is_system_admin == TRUE) {
			// system admin may add new eq_groups
			?>
		<form action="ajax_add_eq_group.php" id="formAddEqGroup" class="" name="formAddEqGroup" method="post">
            <button type="button" id="btnDisplayAddEqGroup" class="btn btn-primary" name="btnDisplayAddEqGroup">Add a new equipment group
            </button>

            <div id="eqGroupFields" class="displayNone">
                <fieldset title="">
                    <legend>Add a new equipment group</legend>
                    <label>Name</label>
					<input type="text" id="eqGroupName" class="" name="eqGroupName" value="" placeholder="Name of group" /><br />
                    <label>Description</label>
					<textarea id="eqGroupDescription" class="" name="eqGroupDescription" placeholder="Description of group"></textarea><br />
                    <button type="button" id="btnSubmitAddEqGroup" class="btn btn-success">Add Group</button>
                    <button type="button" id="btnCancelAddEqGroup" class="btn">Cancel</button>
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
        <h2><?php echo LANG_INSTITUTION_NAME; ?></h2>
        <h1><?php echo LANG_APP_NAME; ?></h1>

        <br />

        <p>This is our system for scheduling equipment reservations.</p>

        <p>To sign in, please use your Williams username and password.</p>

    </div>
	<?php
	}

	require_once('foot.php');
?>