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

            // Show ajax form
            $("#btnDisplayAddEqGroup").click(function () {
                $("#btnDisplayAddEqGroup").addClass('displayNone');
                $("#eqGroupFields").removeClass('displayNone');
            });

            $("#btnCancelAddEqGroup").click(function () {
                // custom form cleanup
                cleanUpForm("formAddEqGroup")
            });

            // Remove later: debugging jquery validator plugin
            $("a.check").click(function () {
                alert("Valid: " + $("#formAddEqGroup").valid());
                return false;
            });


            // ***************************
            // Form validation
            // ***************************

            var validator = $('#formAddEqGroup').validate({
                rules:{
                    eqGroupName:{
                        minlength:2,
                        required:true
                    },
                    eqGroupDescription:{
                        minlength:2,
                        required:true
                    }
                },
                highlight:function (element) {
                    $(element).closest('.control-group').removeClass('success').addClass('error');
                },
                success:function (element) {
                    element
                            .text('OK!').addClass('valid')
                            .closest('.control-group').removeClass('error').addClass('success');
                },
                submitHandler:function (form) {
                    var url = $("#formAddEqGroup").attr('action');			// get url from the form element
                    var formName = $("#formAddEqGroup").attr('name');		// get name from the form element
                    var data1 = $('#' + formName + ' #eqGroupName').val();
                    var data2 = $('#' + formName + ' #eqGroupDescription').val();
                    // alert('url=' + url + '\n' + 'formName=' + formName + '\n' + 'data1=' + data1 + '\n' + 'data2=' + data2);

                    $.ajax({
                        type:'POST',
                        url:url,
                        data:{
                            ajaxVal_GroupName:data1,
                            ajaxVal_GroupDescription:data2
                        },
                        dataType:'html',
                        success:function (data) {
                            // custom form cleanup
                            cleanUpForm("formAddEqGroup")

                            if (data) {
                                // update the element with new data from the ajax call
                                $("UL#displayEqGroups").append(data);
                            } else {
                                // show error
                                $("UL#displayEqGroups").append('<li><span class="label label-important">Important</span> An error occurred!</li>');
                            }
                        }
                    });

                }
            })


            // ***************************
            // Custom functions
            // ***************************

            function cleanUpForm(formName) {
                // reset form
                $("#" + formName).trigger("reset");
                validator.resetForm();
                // hide form, show button to activate form
                $("#eqGroupFields").addClass('displayNone');
                $("#btnDisplayAddEqGroup").removeClass('displayNone');
                // manually remove input highlights
                $(".control-group").removeClass('success').removeClass('error');
            }

        });
    </script>

    Remove this later: <a href="#" class="check">is form valid?</a><br>

	<?php
		echo "<hr />";
		echo "<h3>Equipment Groups</h3>";
		echo "<ul id=\"displayEqGroups\">";

		# is system admin?
		if ($USER->flag_is_system_admin == 1) {
			# get groups for this ordinary user
			$UserEqGroups = EqGroup::getAllEqGroupsForAdminUser($USER);
			if (count($UserEqGroups) > 0) {
				foreach ($UserEqGroups as $ueg) {
					echo "<li><a href=\"equipment_group.php?eid=" . $ueg->eq_group_id . "\" title=\"\">" . $ueg->name . "</a>: " . $ueg->descr . "</li>";
				}
			} else {
				echo "<li>You do not belong to any equipment groups.</li>";
			}
			echo "</ul>";
			# system admin may add new eq_groups
			?>
        <form action="ajax_add_eq_group.php" id="formAddEqGroup" class="form-horizontal" name="formAddEqGroup" method="post">
            <button type="button" id="btnDisplayAddEqGroup" class="btn btn-primary" name="btnDisplayAddEqGroup">Add a
                new equipment group
            </button>

            <div id="eqGroupFields" class="displayNone">
                <fieldset title="">
                    <legend>Add a new equipment group</legend>
                    <div class="control-group">
                        <label class="control-label" for="eqGroupName">Name</label>

                        <div class="controls">
                            <input type="text" id="eqGroupName" class="input-medium" name="eqGroupName" value="" placeholder="Name of group" maxlength="200" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="eqGroupDescription">Description</label>

                        <div class="controls">
                            <input type="text" id="eqGroupDescription" class="input-medium" name="eqGroupDescription" value="" placeholder="Description of group" maxlength="200" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="btnSubmitAddEqGroup"></label>
                        <div class="controls">
                            <button type="submit" id="btnSubmitAddEqGroup" class="btn btn-success">Add Group</button>
                            <button type="reset" id="btnCancelAddEqGroup" class="btn">Cancel</button>
                        </div>
                    </div>

                </fieldset>
            </div>
        </form>
		<?php
		} else {
			# get groups for this ordinary user
			$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);
			if (count($UserEqGroups) > 0) {
				foreach ($UserEqGroups as $ueg) {
					echo "<li><a href=\"equipment_group.php?eid=" . $ueg->eq_group_id . "\" title=\"\">" . $ueg->name . "</a>: " . $ueg->descr;
					if ($ueg->permission->role->priority == 1) {
						echo " (You manage this group)";
					}
					echo "</li>";
				}
			} else {
				echo "<li>You do not belong to any equipment groups.</li>";
			}
			echo "</ul>";
		}
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