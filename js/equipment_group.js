$(document).ready(function () {
    // default conditions

    // ***************************
    // Listeners
    // ***************************

    // Toggle Equipment Group Settings (text or input fields)
    $("#toggleGroupSettings").click(function () {
        // toggle form or plain-text
        $("#managerView, #managerEdit, .editing-control, .view-control").toggleClass("hide");
        // toggle button label
        if ($("#managerView").hasClass('hide')) {
            $("#toggleGroupSettings").html('<i class="icon-white icon-ok"></i> View');
            // hide the other form
            $("#btnCancelReservation").click();
            // show special manager actions
            $(".manager-action").removeClass("hide");
        }
        else {
            $("#toggleGroupSettings").html('<i class="icon-white icon-pencil"></i> Edit');
            // hide special manager actions
            $(".manager-action").addClass("hide");
        }
    });

    // Toggle Reserve Equipment (show or hide form fields)
    $("#toggleReserveEquipment").click(function () {
        // toggle form or plain-text
        $(".reservationForm").toggleClass("hide");
        // toggle button label
        if ($(".reservationForm").hasClass('hide')) {
            $("#toggleReserveEquipment").html('<i class="icon-white icon-pencil"></i> Reserve Equipment');
        }
        else {
            $("#toggleReserveEquipment").html('<i class="icon-white icon-ok"></i> View Equipment');
            // hide the other form
            $("#btnCancelEditGroup").click();
            // hide special manager actions
            $(".manager-action").addClass("hide");
        }
    });

    // Cancel and cleanup
    $("#btnCancelEditGroup").click(function () {
        cleanUpForm("formEditGroup")
        // hide form fields
        $("#managerEdit").addClass("hide");
        $("#managerView").removeClass("hide");
        $("#toggleGroupSettings").html('<i class="icon-white icon-pencil"></i> Edit');
        // hide special manager actions
        $(".manager-action").addClass("hide");
        // reset the submit button (avoid disabled state)
        $("#btnSubmitEditGroup").button('reset');
    });
    // Cancel and cleanup
    $("#btnCancelReservation").click(function () {
        cleanUpForm("formScheduleReservations")
        // hide form fields, restore button label
        $(".reservationForm").addClass("hide");
        $("#toggleReserveEquipment").html('<i class="icon-white icon-pencil"></i> Reserve Equipment');
    });

    // Update form values
    $("#goStartMinute").change(function () {
        $("#startMinute").val($("#goStartMinute").val());
    });
    $("#goMinDurationMinutes").change(function () {
        $("#minDurationMinutes").val($("#goMinDurationMinutes").val());

    });
    $("#goMaxDurationMinutes").change(function () {
        $("#maxDurationMinutes").val($("#goMaxDurationMinutes").val());
    });
    $("#goDurationIntervalMinutes").change(function () {
        $("#durationIntervalMinutes").val($("#goDurationIntervalMinutes").val());
    });

    // Reserve Equipment: calendar
    $("#reservationStartDate, #reservationEndDate").datepicker({
        dateFormat: 'M dd, yy'
    }).val($.datepicker.formatDate('M dd, yy', new Date()));
    // Reserve Equipment: calendar: hack to make icon trigger
    $("#iconHackForceStartDate").click(function () {
        $("#reservationStartDate").datepicker('show');
    });
    $("#iconHackForceEndDate").click(function () {
        $("#reservationEndDate").datepicker('show');
    });
    // Reserve Equipment: timepicker
    $("#reservationStartTime").timepicker({
        minuteStep: 15,
        defaultTime: 'current', /* or set to a specific time: '11:45 AM' */
        showMeridian: true  /* true is 12hr mode, false is 12hr mode */
    });
    $("#reservationEndTime").timepicker({
        minuteStep: 15,
        defaultTime: 'current', /* or set to a specific time: '11:45 AM' */
        showMeridian: true  /* true is 12hr mode, false is 12hr mode */
    });

    // Remove later: debugging jquery validator plugin
    $("a.check").click(function () {
        alert("is 'formEditGroup' Valid?: " + $("#formEditGroup").valid() + "\n" + "is 'formScheduleReservations' Valid?: " + $("#formScheduleReservations").valid());
        return false;
    });


	// Convert initial integer values to pretty text for standard output to screen
	$('#print_minDurationMinutes').text(util_minutesToWords($('#print_minDurationMinutes').text()));
	$('#print_maxDurationMinutes').text(util_minutesToWords($('#print_maxDurationMinutes').text()));
	$('#print_durationIntervalMinutes').text(util_minutesToWords($('#print_durationIntervalMinutes').text()));

	// convert minute to pretty words using: days, hours, minutes
	function util_minutesToWords(minutes) {
		var ret = "";

		/*** get the days ***/
		var days = Math.floor( minutes / (60 * 24) );
		if (days > 0) {
			ret += days + " days ";
		}

		/*** get the hours ***/
		var hours = Math.floor( (minutes / 60) % 24);
		if (hours > 0) {
			ret += hours + " hours ";
		}

		/*** get the minutes ***/
		var mins = Math.floor( minutes % 60);
		if (mins > 0) {
			ret += mins + " minutes ";
		}

		return ret;
	}

    // ***************************
    // Form validation
    // ***************************
    var validator1 = $('#formEditGroup').validate({
        rules: {
            groupName: {
                minlength: 2,
                required: true
            },
            groupDescription: {
                minlength: 2,
                required: true
            },
            startMinute: {
                /* TODO: CSV List: strip spaces, ensure only integers and commas */
                required: true
            },
            minDurationMinutes: {
                digits: true,
                min: 1,
                required: true
            },
            maxDurationMinutes: {
                digits: true,
                min: 1,
                required: true
            },
            durationIntervalMinutes: {
                digits: true,
                min: 1,
                required: true
            }
        },
        highlight: function (element) {
            $(element).closest('.control-group').removeClass('success').addClass('error');
        },
//					success: function (element) {
//						element
//							.text('OK!').addClass('valid')
//							.closest('.control-group').removeClass('error').addClass('success');
//					},
        submitHandler: function (form) {
            // show loading text (button)
            $("#btnSubmitEditGroup").button('loading');

            // Update printed text values on screen with submitted values
            $("#print_groupName").text($("#groupName").val());
            $("#print_groupDescription").text($("#groupDescription").val());
            $("#print_startMinute").text($("#startMinute").val());
			// Convert input values to pretty text for standard output to screen
            $("#print_minDurationMinutes").text(util_minutesToWords($("#minDurationMinutes").val()));
			$("#print_maxDurationMinutes").text(util_minutesToWords($("#maxDurationMinutes").val()));
            $("#print_durationIntervalMinutes").text(util_minutesToWords($("#durationIntervalMinutes").val()));

            var url = $("#formEditGroup").attr('action');
            var formName = "formEditGroup";
//							alert('url=' + url + '\n' + 'formName=' + formName + '\n');

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    ajaxVal_ID: $('#groupID').val(),
                    ajaxVal_Name: $('#groupName').val(),
                    ajaxVal_Description: $('#groupDescription').val(),
                    ajaxVal_StartMinute: $('#startMinute').val(),
                    ajaxVal_MinDurationMinute: $('#minDurationMinutes').val(),
                    ajaxVal_MaxDurationMinute: $('#maxDurationMinutes').val(),
                    ajaxVal_DurationIntervalMinutes: $('#durationIntervalMinutes').val()
                },
                dataType: 'html',
                success: function (data) {
                    // hide and reset form
                    $("#btnCancelEditGroup").click();

                    if (data) {
                        // nothing more to do
                    }
                    else {
                        // show error message
                        $("DIV.container legend:nth-child(2)").before('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> This record was not found in the database</div>');
                    }
                }
            });

        }
    })

    var validator2 = $('#frmAjaxAddItem').validate({
        rules: {
            ajaxItemName: {
                minlength: 2,
                required: true
            },
            ajaxItemDescription: {
                minlength: 2,
                required: true
            }
        },
        highlight: function (element) {
            $(element).closest('.control-group').removeClass('success').addClass('error');
        },
        success: function (element) {
            element
                .text('OK!').addClass('valid')
                .closest('.control-group').removeClass('error').addClass('success');
        },
        submitHandler: function (form) {
            // show loading text (button)
            $("#btnAjaxSubmitAddItem").button('loading');

            var url = $("#frmAjaxAddItem").attr('action');			// get url from the form element
            var formName = $("#frmAjaxAddItem").attr('name');		// get name from the form element
            var data1 = $('#' + formName + ' #ajaxSubgroupID').val();
            var data2 = $('#' + formName + ' #ajaxItemOrdering').val();
            var data3 = $('#' + formName + ' #ajaxItemName').val();
            var data4 = $('#' + formName + ' #ajaxItemDescription').val();
            var data5 = $('#' + formName + ' #ajaxItemIsMultiSelect').val();
            // alert('url=' + url + '\n' + 'formName=' + formName + '\n' + 'data1=' + data1 + '\n' + 'data2=' + data2 + '\n' + 'data3=' + data3 + '\n' + 'data4=' + data4 + '\n' + 'data5=' + data5);

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    ajaxVal_ID: data1,
                    ajaxVal_Order: data2,
                    ajaxVal_Name: data3,
                    ajaxVal_Description: data4,
                    ajaxVal_MultiSelect: data5
                },
                dataType: 'html',
                success: function (data) {
                    // hide and reset form
                    $("#btnAjaxCancelAddItem").click();

                    if (data) {
                        // remove error messages
                        $('DIV.alert-error').remove();

                        // update the element with new data from the ajax call
                        $("UL#displaySubgroup" + data1 + " li:nth-last-child(2)").append(data);
                    }
                    else {
                        // show error message
                        $("UL#displaySubgroup" + data1 + " li:nth-last-child(2)").append('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
                    }
                }
            });

        }
    })

    var validator3 = $('#frmAjaxAddSubgroup').validate({
        rules: {
            ajaxSubgroupName: {
                minlength: 2,
                required: true
            },
            ajaxSubgroupDescription: {
                minlength: 2,
                required: true
            },
            ajaxSubgroupIsMultiSelect: {
                required: true
            }
        },
        highlight: function (element) {
            $(element).closest('.control-group').removeClass('success').addClass('error');
        },
        success: function (element) {
            element
                .text('OK!').addClass('valid')
                .closest('.control-group').removeClass('error').addClass('success');
        },
        submitHandler: function (form) {
            // show loading text (button)
            $("#btnAjaxSubmitAddSubgroup").button('loading');

            var url = $("#frmAjaxAddSubgroup").attr('action');			// get url from the form element
            var formName = $("#frmAjaxAddSubgroup").attr('name');		// get name from the form element
            var data1 = $('#' + formName + ' #ajaxGroupID').val();
            var data2 = $('#' + formName + ' #ajaxSubgroupOrdering').val();
            var data3 = $('#' + formName + ' #ajaxSubgroupName').val();
            var data4 = $('#' + formName + ' #ajaxSubgroupDescription').val();
            var data5 = $('#' + formName + ' input:radio[name=ajaxSubgroupIsMultiSelect]:checked').val();
            // alert('url=' + url + '\n' + 'formName=' + formName + '\n' + 'data1=' + data1 + '\n' + 'data2=' + data2);

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    ajaxVal_ID: data1,
                    ajaxVal_Order: data2,
                    ajaxVal_Name: data3,
                    ajaxVal_Description: data4,
                    ajaxVal_MultiSelect: data5
                },
                dataType: 'html',
                success: function (data) {
                    // hide and reset form
                    $("#btnAjaxCancelAddSubgroup").click();

                    if (data) {
                        // remove error messages
                        $('DIV.alert-error').remove();

                        // update the element with new data from the ajax call
                        $("UL#displayAllSubgroups").append(data);
                    }
                    else {
                        // show error message
                        $("UL#displayAllSubgroups").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
                    }
                }
            });

        }
    })

    $(".delete-schedule-btn").click(function () {
        GLOBAL_confirmHandlerData= $(this).attr('data-for-schedule');
        var confirmText = "<p>Are you sure you want to remove that schedule of reservations?</p>\n";
        eqrUtil_launchConfirm(confirmText,handleDeleteSchedule);
    });
    function handleDeleteSchedule() {
        eqrUtil_setTransientAlert('progress','saving...',$('#list-of-schedule-'+GLOBAL_confirmHandlerData));
        $.ajax({
            url:'ajax_schedule.php',
            dataType: 'json',
            data: {'schedule':GLOBAL_confirmHandlerData,
                'scheduleAction':'deleteSchedule',
                'actionVal':GLOBAL_confirmHandlerData
            }
        })
            .done(function (data,status,xhr) {
                if (data.status == 'success') {
                    eqrUtil_setTransientAlert('success','saved',$('#list-of-schedule-'+GLOBAL_confirmHandlerData));
                    $('#list-of-schedule-'+GLOBAL_confirmHandlerData).remove();
                }
                else {
                    eqrUtil_setTransientAlert('error','ERROR - not saved!',$('#list-of-schedule-'+GLOBAL_confirmHandlerData));
                }
            })
            .fail(function (data,status,xhr) {
                eqrUtil_setTransientAlert('error','ERROR - not saved!',$('#list-of-schedule-'+GLOBAL_confirmHandlerData));
            })
        ;
    }


    function cleanUpForm(formName) {
        // reset form
        validator1.resetForm(this);
        validator2.resetForm(this);
        validator3.resetForm(this);
        // manually remove input highlights
        $(".control-group").removeClass('success').removeClass('error');
    }

    // Modals
	$("body").on("click", "a.ajaxActionItem", function(){
		// The 'on' method is necessary to bind classes to click event for dynamically created form elements
		// pass values to modal
		var n = $(this).attr("data-subgroup-name");
		var i = $(this).attr("data-subgroup-id");
		// fetch item count, then increment for modal hidden input
		var o = parseInt($(this).parent('LI').prev('LI').attr("data-item-order")) + 1;
		// fetch subgroup characteristic 'IsMultiSelect' for modal hidden input
		var m = $(this).attr("data-is-multiselect");
		$("H3#modalAddItemLabel").text(n);
		$("INPUT#ajaxSubgroupID").val(i);
		$("INPUT#ajaxItemOrdering").val(o);
		$("INPUT#ajaxItemIsMultiSelect").val(m);
	});
    $('a.ajaxActionSubgroup').click(function () {
        // pass values to modal
        // fetch subgroup count, then increment for modal hidden input
        var o = parseInt($('SPAN[data-subgroup-order]').last().attr('data-subgroup-order')) + 1;
        $("INPUT#ajaxSubgroupOrdering").val(o);
		alert('ajaxSubgroupOrdering = ' + o); //return false;
    });
    $('#btnAjaxCancelAddItem').click(function () {
        cleanUpForm("frmAjaxAddItem")
        // clear form: reset
        $("#frmAjaxAddItem input[type=text], #frmAjaxAddItem textarea").val("");
        $("#frmAjaxAddItem input[type=radio]").attr("checked", false);
        // reset the submit button (avoid disabled state)
        $("#btnAjaxSubmitAddItem").button('reset');
    });
    $('#btnAjaxCancelAddSubgroup').click(function () {
        cleanUpForm("frmAjaxAddSubgroup")
        // clear form: reset
        $("#frmAjaxAddSubgroup input[type=text], #frmAjaxAddSubgroup textarea").val("");
        $("#frmAjaxAddSubgroup input[type=radio]").attr("checked", false);
        // reset the submit button (avoid disabled state)
        $("#btnAjaxSubmitAddSubgroup").button('reset');
    });
});