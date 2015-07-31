$(document).ready(function () {
	// default conditions

	// ***************************
	// Listeners
	// ***************************

	function changeShownItems(month, year, show_which){
		if(show_which==0){
            var has = 1;
			$('.item-listing').each(function(){
				if($(this).data('year')==year && $(this).data('month')==month) {
                    has++;
					//$('.item-listing').addClass('hide');
                    if ($(this).hasClass('hide')) {
                        $(this).removeClass('hide');
                    }
                }
                else {
                    $(this).addClass("hide");
                }
			});
            if(has===1){
                $(".no-reserv-month").removeClass('hide');
                $(".no-reserv-year").addClass('hide');
            }
		}else if(show_which==1){
            var hasMonth = 1;
            $('.item-listing').each(function(){
				if($(this).data('year')==year) {
                    hasMonth++;
                    if ($(this).hasClass('hide')) {
                        $(this).removeClass("hide");
                    }
                }else{
                    $(this).addClass("hide");
				}
			});
            if(hasMonth === 1 && $(".no-reserv-year").hasClass('hide')){
                $(".no-reserv-year").removeClass('hide');
            }
			$(".no-reserv-month").addClass('hide');

		}else if(show_which==2){
            $('.item-listing').each(function(){
				if($(this).hasClass('hide')){
					$(this).removeClass("hide");
                    $(".no-reserv-month").addClass('hide');
                    $(".no-reserv-year").addClass('hide');
                }
			});
		}
	}

	//****** related to showing reservations list
	$(document).on("click", "#show-reservations-buttons .show-this-month", function(){
		var show_which = $('.show-this-month').attr('data-show-month');
		var month = parseInt($('.show-this-month').attr('data-thisMonth'));
		var year = parseInt($('.show-this-month').attr('data-thisYear')); //have to use attr here
		changeShownItems(month,year,show_which);


		$('.show-this-month').addClass('hide');
		$('.show-this-year').removeClass('hide');
		$('.show-all').removeClass('hide');
	});

	$(document).on("click", "#show-reservations-buttons .show-this-year", function(){
		var show_which = $('.show-this-year').attr('data-show-this');
		var year = parseInt($('.show-this-year').attr('data-thisYear')); //have to use attr here
		var month = 0;
		changeShownItems(month,year,show_which);

		$('.show-this-month').removeClass('hide');
		$('.show-this-year').addClass('hide');
		$('.show-all').removeClass('hide');
	});

	$(document).on("click", "#show-reservations-buttons .show-all", function(){
		var show_which = $('.show-all').attr('data-show-all');
		var month = 0;
		var year = 0;
		changeShownItems(month,year,show_which);

		$('.show-this-month').removeClass('hide');
		$('.show-this-year').removeClass('hide');
		$('.show-all').addClass('hide');
	});

	// Toggle Equipment Group Settings (text or input fields)
	$("#toggleGroupSettings").click(function () {
		// toggle form or plain-text
		$("#managerView, #managerEdit, .view-control").toggleClass("hide");
		// toggle button label
		if ($("#managerView").hasClass('hide')) {
			$("#toggleGroupSettings").html('<i class="icon-white icon-ok"></i> View Equipment Group');
			// hide the other form
			$("#btnReservationCancel").click();
			// show special manager actions
			$(".manager-action").removeClass("hide");
            $(".subgroup-ul").removeClass("hide");

            //list with delete buttons
            $('.schedule').removeClass('hide');
            $(".editing-control").removeClass("hide");
            $('.item-listing').removeClass('hide');

            //buttons
            $('.show-this-month').addClass('hide');
            $('.show-this-year').addClass('hide');
            $('.show-all').addClass('hide');
            $(".no-reserv-month").addClass('hide');
            $(".no-reserv-year").addClass('hide');

            //calendar/list
            $('.calendar').addClass('hide');
            $('.calendar_day').addClass('hide');
            $(".show_reservation_list").addClass('hide');
            $(".show_reservation_calendar").addClass('hide');
        }
		else {
			$("#toggleGroupSettings").html('<i class="icon-white icon-pencil"></i> Edit Equipment Group');
			// hide special manager actions
			$(".manager-action").addClass("hide");
            $(".subgroup-ul").addClass("hide");
            $(".subgroup-ul").has("li.item-in-a-subgroup").removeClass("hide");

            if($("#item").hasClass("item-listing")){
                $(".editing-control").addClass("hide");
                $('.show-this-month').removeClass('hide');
                $('.show-this-year').removeClass('hide');
                $('.show-all').addClass('hide');
            }else{
                $(".none-at-all").removeClass('hide');
                $(".no-reserv-month").addClass('hide');
                $(".no-reserv-year").addClass('hide');
                $('.show-this-month').addClass('hide');
                $('.show-this-year').addClass('hide');
                $('.show-all').addClass('hide');
            }
            $(".show_reservation_list").removeClass('hide');
            $(".show_reservation_calendar").removeClass('hide');
		}
	});

	// Toggle Reserve Equipment (show or hide form fields)
	$("#toggleReserveEquipment").click(function () {
		// toggle form or plain-text
		$(".reservationForm").toggleClass("hide");
		// toggle button label
		if ($(".reservationForm").hasClass('hide')) {
			$("#toggleReserveEquipment").html('<i class="icon-white icon-pencil"></i> Reserve Equipment');
            $(".subgroupRadiosControls").addClass("hide");
            $(".subgroupCheckboxesControls").addClass("hide");
		}
		else {
			$("#toggleReserveEquipment").html('<i class="icon-white icon-ok"></i> View Equipment');
			// hide the other form
			$("#btnCancelEditGroup").click();
			// hide special manager actions
			$(".manager-action").addClass("hide");
            $(".subgroupRadiosControls").removeClass("hide");
            $(".subgroupCheckboxesControls").removeClass("hide");
        }
	});

	// Make easy to check or un-check a subgroup's items
	$(".uncheckSubgroupRadios, .uncheckSubgroupCheckboxes").click(function () {
		$(this).parent().parent('UL').find("input[type='radio'], input[type='checkbox']").prop("checked", false);
	});
	$(".checkSubgroupCheckboxes").click(function () {
		$(this).parent().parent('UL').find("input[type='checkbox']").prop("checked", true);
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

	// any changes to form should hide the override button (if it exists)
	$("#frmAjaxScheduleReservations input[type=checkbox], #frmAjaxScheduleReservations input[type=radio]").change(function () {
		if (!$("#btnReservationOverrideConflicts").hasClass("hide")) {
			$("#btnReservationOverrideConflicts").addClass("hide");
		}
	});

	// Reserve Equipment: calendar
	$("#scheduleStartOnDate").datepicker({
		dateFormat: 'yy-mm-dd',
		minDate: -730,
		maxDate: +730
	}).val($.datepicker.formatDate('yy-mm-dd', new Date()));
	// Hack to make calendar icon functional
	$("#iconHackScheduleStartOnDate").click(function () {
		$("#scheduleStartOnDate").datepicker('show');
	});
	// Reserve Equipment: timepicker
	$("#scheduleStartTimeRaw").timepicker({
		minuteStep: util_durationToInt($("#managerView").attr("data-duration-chunk")), /* takes into account time restrictions */
		defaultTime: 'current', /* or set to a specific time: '11:45 AM' */
		showMeridian: true  /* true is 12hr mode, false is 12hr mode */
	});

    // Convert duration (ex: '5M') to integer (ex: 5)
    function util_durationToInt(dur){
        var intDur = dur.substring(0,dur.length);
        return parseInt(intDur);
    }

	// Convert initial integer values to pretty text for standard output to screen
	$('#print_minDurationMinutes').text(util_minutesToWords($('#print_minDurationMinutes').text()));
	$('#print_maxDurationMinutes').text(util_minutesToWords($('#print_maxDurationMinutes').text()));
	$('#print_durationIntervalMinutes').text(util_minutesToWords($('#print_durationIntervalMinutes').text()));

	// Convert AM/PM time to 24 hour database-ready format
	function util_12To24HourFormat(time) {
		var hours = Number(time.match(/^(\d+)/)[1]);
		var minutes = Number(time.match(/:(\d+)/)[1]);
		var AMPM = time.match(/\s(.*)$/)[1];
		if (AMPM == "PM" && hours < 12) hours = hours + 12;
		if (AMPM == "AM" && hours == 12) hours = hours - 12;
		var sHours = hours.toString();
		var sMinutes = minutes.toString();
		if (hours < 10) sHours = "0" + sHours;
		if (minutes < 10) sMinutes = "0" + sMinutes;
		return(sHours + ":" + sMinutes + ":00");
	}

	// Convert minute to pretty words using: days, hours, minutes
	function util_minutesToWords(minutes) {
		var ret = "";

		/*** get the days ***/
		var days = Math.floor(minutes / (60 * 24));
		if (days > 0) {
			ret += days + " days ";
		}

		/*** get the hours ***/
		var hours = Math.floor((minutes / 60) % 24);
		if (hours > 0) {
			ret += hours + " hours ";
		}

		/*** get the minutes ***/
		var mins = Math.floor(minutes % 60);
		if (mins > 0) {
			ret += mins + " minutes ";
		}

		return ret;
	}


	// ***************************
	// Form validation
	// ***************************

	var validateAjaxGroup = $('#frmAjaxGroup').validate({
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
		//success: function (element) {
		//	element
		//		.text('OK!').addClass('valid')
		//		.closest('.control-group').removeClass('error').addClass('success');
		//},
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

			var formName = "frmAjaxGroup";

			$.ajax({
				type: 'GET',
				url: $("#frmAjaxGroup").attr('action'),
				data: {
                    ajaxVal_Action: 'save-group',
                    ajaxVal_GroupID: $('#groupID').val(),
                    ajaxVal_Name: $('#groupName').val(),
                    ajaxVal_Description: $('#groupDescription').val(),
                    ajaxVal_StartMinute: $('#startMinute').val(),
                    ajaxVal_MinDurationMinute: $('#minDurationMinutes').val(),
                    ajaxVal_MaxDurationMinute: $('#maxDurationMinutes').val(),
                    ajaxVal_DurationIntervalMinutes: $('#durationIntervalMinutes').val()
				},
				dataType: 'json',
				success: function (data) {
					// hide and reset form
					$("#btnCancelEditGroup").click();

					if (data.status == 'success') {
						// nothing more to do
					}
					else {
						// error message
						$("DIV.container legend:nth-child(2)").before('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> This record was not found in the database</div>');
					}
				}
			});

		}
	});
	var validateAjaxSubgroup = $('#frmAjaxSubgroup').validate({
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
			$("#btnAjaxSubgroupSubmit").button('loading');

			var formName = $("#frmAjaxSubgroup").attr('name');		// get name from the form element
			var action = $('#' + formName + ' #ajaxSubgroupAction').val();
			var group_id = $('#' + formName + ' #ajaxGroupID').val();
			var subgroup_id = $('#' + formName + ' #ajaxItemSubGroup').val();
			var subgroup_ordering = $('#' + formName + ' #ajaxSubgroupOrdering').val();
			var subgroup_name = $('#' + formName + ' #ajaxSubgroupName').val();
			var subgroup_description = $('#' + formName + ' #ajaxSubgroupDescription').val();
			var subgroup_multiselect = $('#' + formName + ' input:radio[name=ajaxSubgroupIsMultiSelect]:checked').val();

			$.ajax({
				type: 'GET',
				url: $("#frmAjaxSubgroup").attr('action'),
				data: {
					ajaxVal_Action: action,
					ajaxVal_GroupID: group_id,
					ajaxVal_SubgroupID: subgroup_id,
					ajaxVal_Order: subgroup_ordering,
					ajaxVal_Name: subgroup_name,
					ajaxVal_Description: subgroup_description,
					ajaxVal_MultiSelect: subgroup_multiselect
				},
				dataType: 'json',
				success: function (data) {
					// hide and reset form
					$("#btnAjaxItemCancel").click();
					$("#btnAjaxSubgroupCancel").click();

					if (data.status == 'success') {
						// remove error messages
						$('DIV.alert-error').remove();

						if (data.which_action == 'add-subgroup') {
							// update element with resultant ajax data
							$("UL#displayAllSubgroups").append(data.html_output);
						}
						else if (data.which_action == 'edit-subgroup') {
							// update button data attributes
							$("#btn-edit-subgroup-id-" + subgroup_id).attr("data-for-subgroup-name", subgroup_name);
							$("#btn-edit-subgroup-id-" + subgroup_id).attr("data-for-subgroup-descr", subgroup_description);
							$("#btn-edit-subgroup-id-" + subgroup_id).attr("data-for-ismultiselect", subgroup_multiselect);
							// update visible info
							$("span#subgroupid-" + subgroup_id).html("<strong>" + subgroup_name + ": </strong>" + subgroup_description);
						}
					}
					else {
						// error message
						$("UL#displayAllSubgroups").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
					}
				}
			});

		}
	});


	var validateAjaxItem = $('#frmAjaxItem').validate({
		rules: {
			ajaxItemName: {
				minlength: 2,
				required: true
			},
			ajaxItemDescription: {
				minlength: 2,
				required: true
			},
            ajaxItemImage: {
                required: false
            }
		},
		highlight: function (element) {
			$(element).closest('.control-group').removeClass('success').addClass('error');
		},
		success: function (element) {
			element
				.text('OK!').addClass('valid')
//                .closest('.control-group').removeClass('error').addClass('success');
                .closest('.control-group').removeClass('error');
		},
		submitHandler: function (form) {
			// show loading text (button)
			$("#btnAjaxItemSubmit").button('loading');

			console.log($("#ajaxItemSubGroup").val()); //DEV
			var formName = $("#frmAjaxItem").attr('name');		// get name from the form element
			var action = $('#' + formName + ' #ajaxItemAction').val();
			console.log(action); //DEV
			var subgroup_id = $('#ajaxItemSubGroup').val();
			var subgroup_name = $('#' + formName + ' #ajaxSubgroupName').val();
			var item_id = $('#' + formName + ' #ajaxItemID').val();
			var item_ordering = $('#' + formName + ' #ajaxItemOrdering').val();
			var item_name = $('#' + formName + ' #ajaxItemName').val();
			var item_description = $('#' + formName + ' #ajaxItemDescription').val();
			var item_multiselect = $('#' + formName + ' #ajaxItemIsMultiSelect').val();

            var item_image_file_name = 'none';
            if ($('#item-image-preview-area img')[0]) {
                if ($('#item-image-preview-area img')[0].file) {
                    item_image_file_name = $('#item-image-preview-area img')[0].file.name;
                } else {
                    item_image_file_name = 'nochange';
                }
            }

            var ajax_data = {
                ajaxVal_Action: action,
				ajaxVal_SubgroupID: subgroup_id,
                ajaxVal_SubgroupName: subgroup_name,
                ajaxVal_ItemID: item_id,
                ajaxVal_Order: item_ordering,
                ajaxVal_Name: item_name,
                ajaxVal_Description: item_description,
                ajaxVal_MultiSelect: item_multiselect,
                ajaxVal_ImageFileName: item_image_file_name
            };
            console.log('ajax data: ');
            console.dir(ajax_data);

			$.ajax({
                url: $("#frmAjaxItem").attr('action'),
				type: 'GET',
//				data: {
//					ajaxVal_Action: action,
//					ajaxVal_SubgroupID: subgroup_id,
//					ajaxVal_SubgroupName: subgroup_name,
//					ajaxVal_ItemID: item_id,
//					ajaxVal_Order: item_ordering,
//					ajaxVal_Name: item_name,
//					ajaxVal_Description: item_description,
//					ajaxVal_MultiSelect: item_multiselect,
//                    ajaxVal_ImageFileName: item_image_file_name
//				},
                data: ajax_data,
                dataType: 'json',
				success: function (data) {
					// hide and reset form
					$("#btnAjaxItemCancel").click();
					$("#btnAjaxSubgroupCancel").click();

					if (data.status == 'success') {
						// remove error messages
						$('DIV.alert-error').remove();

						// hide message: 'No items exist.'
						$("UL#ul-of-subgroup-" + subgroup_id + " span.noItemsExist").addClass("hide");

						if (data.which_action == 'add-item') {
							// update element with resultant ajax data
							$("UL#ul-of-subgroup-" + subgroup_id + " li.manager-action").before(data.html_output);
						}
						else if (data.which_action == 'edit-item') {
//
//                            console.log('edit response data: ');
//                            console.dir(data);
//
							var currSubID = $("#btn-edit-item-id-" + item_id).attr("data-for-subgroup-id");
							if (currSubID != subgroup_id) {
								$('#ul-of-subgroup-' +subgroup_id+' li').last().before($('#list-of-item-' + item_id));
							}

							// update button data attributes
							$("#btn-edit-item-id-" + item_id).attr("data-for-item-name", item_name);
							$("#btn-edit-item-id-" + item_id).attr("data-for-item-descr", item_description);
							$("#btn-edit-item-id-" + item_id).attr("data-for-subgroup-name", subgroup_name);
							$("#btn-edit-item-id-" + item_id).attr("data-for-subgroup-id", subgroup_id);

							// update visible info
							$("span#itemid-" + item_id).html("<strong>" + item_name + ": </strong>" + item_description);
                            if (data.has_no_image) {
                                $("#itemImageSpanFor"+item_id).html('<i>[no image available]</i>');
                            }
						}
//                        console.dir($('#item-image-preview-area img'));
                        if ($('#item-image-preview-area img')[0]) {
                            sendFile($('#item-image-preview-area img')[0].file,data.for_item_id);
                        }
                    }
					else {
						// error message
						$("UL#ul-of-subgroup-" + subgroup_id + " li.manager-action").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4>'+data.message+'.</div>');
					}
				}
			});
			$("#btnAjaxItemSubmit").button('reset');

		}
	});


    function sendFile(f,item_id) {
//        alert('sendFile called! Using '+ f.name + ' for item '+item_id);
//        alert('TODO: put placeholder text/elt in for image tag');
//        return;
        var uri = "ajax_actions/ajax_item_image_upload_handler.php";
        var xhr = new XMLHttpRequest();
        var fd = new FormData();

        xhr.open("POST", uri, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Handle response.
//                alert(xhr.responseText); // handle response.
//                alert('TODO: if status is success, update the appropriate image tags (replacing placeholder text inserted above) on this page; otherwise show error message');
                var data = JSON && JSON.parse(xhr.responseText) || $.parseJSON(xhr.responseText);
//                console.dir(data);
                var spanId = "#itemImageSpanFor"+item_id;
                var targetSpan = $(spanId);
//                console.dir(targetSpan);
                if (data.status == 'success') {
                    targetSpan.html(data.html_output);
                } else {
                    targetSpan.html(data.message);
                }
            }
        };
        fd.append('ajaxVal_file', f);
        fd.append('ajaxVal_ItemID',item_id);
        // Initiate a multipart/form-data upload
//        console.dir(fd);
//        console.log(fd.toString());
        xhr.send(fd);
    }


	$.validator.addMethod("avoidEmptyReservation", function (value, element) {
		//var count_checked = $("#" + formID + " input[type='radio']:checked, #" + formID + " input[type='checkbox']:checked").length;
		var count_checked = $("#frmAjaxScheduleReservations input[type='radio']:checked, #frmAjaxScheduleReservations input[type='checkbox']:checked").length;
		return this.optional(element) || (count_checked > 0);
	}, "You must select at least one item for your reservation.");

	var validateAjaxScheduleReservations = $('#frmAjaxScheduleReservations').validate({
		rules: {
			scheduleStartOnDate: {
				required: true,
				date: true
			},
			scheduleEndOnDate: {
				required: true,
				date: true
			},
			scheduleDuration: {
				required: true,
				avoidEmptyReservation: true // custom validation tied to dynamic form elements (it must be tied to a form element in the DOM)
			}
		},
		highlight: function (element) {
			$(element).closest('.control-group').removeClass('success').addClass('error');
		},
//		success: function (element) {
//			element
//				.text('OK!').addClass('valid')
//				.closest('.control-group').removeClass('error').addClass('success');
//		},
		submitHandler: function (form) {
			if ($("#btnReservationSubmit").val() == 'just_clicked') {
				$("#btnReservationSubmit").button('loading');
			}
			else if ($("#btnReservationOverrideConflicts").val() == 'just_clicked') {
				$("#btnReservationOverrideConflicts").button('loading');
			}
			$.ajax({
				type: 'GET',
				url: $("#frmAjaxScheduleReservations").attr('action'),
				data: $('#frmAjaxScheduleReservations').serialize(),
				dataType: 'json',
				success: function (data) {
					console.log(data);
					if (data.status == 'success') {
						// show message
						eqrUtil_setTransientAlert("success", "Successfully scheduled your reservation(s).");
						// reload page (and thereby clear form values)
						window.location.reload();
					}
					else {
						// conflicts exist: reset buttons
						$("#btnReservationSubmit").button('reset').text('Re-Submit');
						$("#btnReservationOverrideConflicts").button('reset').removeClass("hide");
						// display error message
						$("#show_any_conflicts").text("").show().append(parseConflicts(data));
					}
				}
			});
		}
	});


	function parseConflicts(data) {
		var t = "";
		$.each(data, function (id, val) {
			if (id == "status" && val == "scheduling-conflict") {
				t = t + "<strong>Scheduling conflicts exist!</strong><br />Managers may override existing reservations (the former are deleted and a message is sent to the original creator).<br /><br />";
			}
			else {
				if (id == "conflicts_by_datetime") {
					t = t + "<strong>Sorted by datetime:</strong>";
				}
				else if (id == "conflicts_by_item") {
					t = t + "<strong>Sorted by item:</strong>";
				}

				// now loop through object
				var lastGroup = "";
				$.each(this, function (group, members) {
//					t = t + "group=" + group + ",members=" + members + '<br />';
					if (group != lastGroup && lastGroup != "") {
						// multiple group listings require additional HTML closures
						t = t + "</ul></li></ul>";
					}
					if (typeof(data) == 'object') {
						t = t + "<ul><li>" + group + "<ul>";
					}
					t = t + "<li>" + members + '</li>';
					lastGroup = group;
				});
				t = t + "</ul></li></ul>";
			}
		});
		return t;
	}

	$(".delete-schedule-btn").click(function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-schedule');
		var confirmText = "<p>Are you sure you want to remove that schedule of reservations?</p>\n";
		eqrUtil_launchConfirm(confirmText, handleDeleteSchedule);
	});

	function handleDeleteSchedule() {
		// show status
		eqrUtil_setTransientAlert('progress', 'saving...', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
		$.ajax({
			url: 'ajax_actions/ajax_schedule.php',
			dataType: 'json',
			data: {'schedule': GLOBAL_confirmHandlerData,
				'scheduleAction': 'deleteSchedule',
				'actionVal': GLOBAL_confirmHandlerData
			}
		})
			.done(function (data, status, xhr) {
				if (data.status == 'success') {
					// show status
					eqrUtil_setTransientAlert('success', 'saved', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
					$('#list-of-schedule-' + GLOBAL_confirmHandlerData).remove();
				}
				else {
					// show status
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
				}
			})
			.fail(function (data, status, xhr) {
				// show status
				eqrUtil_setTransientAlert('error', 'ERROR - not saved!', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
			})
		;
	}


	function showConfirmBox(ary) {
		//alert(ary['ajax_action'] + ', ' + ary['ajax_id']);
		bootbox.dialog(ary['title'],
			[
				{
					"label": ary['label'],
					"class": ary['class'],
					"callback": function () {
						// show status
						eqrUtil_setTransientAlert('progress', 'saving...');
						$.ajax({
							type: 'GET',
							url: ary['url'],
							data: {
								'ajaxVal_Action': ary['ajax_action'],
								'ajaxVal_Delete_ID': ary['ajax_id']
							},
							dataType: 'json',
							success: function (data) {
								if (data.status == 'success') {
									// remove element
									updateDOM(ary['ajax_action'], true);
								}
								else {
									// error message
									updateDOM(ary['ajax_action'], false);
								}
							}
						});
					}
				},
				{
					"label": "Cancel",
					"class": "btn btn-link btn-cancel pull-left",
					"callback": function () {
						this.dismiss = "modal";
					}
				}
			],
			{
				// modal options
				"animate": false,
				"backdrop": "static",
				"onEscape": true
			});
	}

	function updateDOM(action, ret) {
		if (action == 'delete-item') {
			if (ret) {
				// show status
				eqrUtil_setTransientAlert('success', 'saved');
				// removed last remaining item? then show message
                if ($('#list-of-item-' + GLOBAL_confirmHandlerData).parent('UL').find('LI').length == 2) { // LI item to be removed + LI button
					// show message: 'No items exist.'
					$('#list-of-item-' + GLOBAL_confirmHandlerData).parent('UL').find('span.noItemsExist').removeClass("hide");
				}
				// remove element
				$('#list-of-item-' + GLOBAL_confirmHandlerData).remove();
			}
			else {
				// error message
				$("#list-of-item-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
		else if (action == 'delete-subgroup') {
			if (ret) {
				// show status
				eqrUtil_setTransientAlert('success', 'saved');
				// remove element
				$('#ul-of-subgroup-' + GLOBAL_confirmHandlerData).remove();
			}
			else {
				// error message
				$("#ul-of-subgroup-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
	}


	// ***************************
	// Passing values to Modals (using either 'custom' or 'Bootbox' modals)
	// Note: The 'on' method binds the document with the function handler's actions for selected items ('.eq-delete-item') to the 'click' event
	// Note: This is useful for binding dynamically generated DOM elements to the document
	// ***************************

	$(document).on("click", ".eq-delete-item", function () {

		GLOBAL_confirmHandlerData = $(this).attr('data-for-item-id');

		var params = {
			title: "Are you sure you want to remove "+$(this).attr('data-for-item-name')+"?",
			label: "Remove Item",
			class: "btn btn-danger pull-left",
			url: "ajax_actions/ajax_eq_subgroup_item.php",
			ajax_action: "delete-item",
			ajax_id: GLOBAL_confirmHandlerData
		};

		showConfirmBox(params);
	});

	$(document).on("click", ".eq-delete-subgroup", function () {

		GLOBAL_confirmHandlerData = $(this).attr('data-for-subgroup-id');

		var params = {
			title: "Are you sure you want to remove "+$(this).attr('data-for-subgroup-name')+" and all items?",
			label: "Remove Subgroup and Items",
			class: "btn btn-danger pull-left",
			url: "ajax_actions/ajax_eq_subgroup.php",
			ajax_action: "delete-subgroup",
			ajax_id: GLOBAL_confirmHandlerData
		};

		showConfirmBox(params);
	});

    $(document).on("click", "#remove-item-image", function () {
        handleClearItemImage();
    });

    function handleClearItemImage() {
        $("#item-image-preview-area").html('');
        $("#ajaxItemImage").val('');
    }

    function handleShowItemImageInEditForItem(item_id) {
//        alert("to be implemented - set item image in form on editing the item");
        if ($("#itemImageFor"+item_id)[0]) {
            $("#item-image-preview-area").html("<img class=\"item-image-preview\" src=\""+$("#itemImageFor"+item_id).attr('src')+"\">");
        }
    }

    $(document).on("change","#ajaxItemImage",function() {
        handleItemImageFileChoice(this);
    });

    function handleItemImageFileChoice(fChooser) {
        if ((fChooser.value == "") || (fChooser.files.length < 1)) {
//            $("#item-image-preview-area").html('');
            return;
        }
        var f = fChooser.files[0];
//        console.dir(fChooser);
//        alert("file selected: "+ f.name+" ("+f.type+", "+f.size+")");
        var imageType = /image.*/;
        if (!f.type.match(imageType)) {
            alert(f.name+" does not seem to be an image");
            return;
        }
        if (f.size > 41000) {
            alert(f.name+" is too large - 40K limit");
            return;
        }
        var img = document.createElement("img");
        img.classList.add("item-image-preview");
        img.file = f;
        $("#item-image-preview-area").html(img);

        var reader = new FileReader();
        reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
        reader.readAsDataURL(f);
    }

	$(document).on("click", ".eq-add-item", function () {
		var subgroup_id = $(this).attr("data-for-subgroup-id");
		var subgroup_name = $(this).attr("data-for-subgroup-name");
		var item_order = parseInt($(this).parent('LI').prev('LI').attr("data-for-item-order"));
		if (isNaN(item_order)) {
			// user deleted all items: set initial value
			item_order = 1;
		}
		else {
			// increment ordering
			item_order += 1;
		}
		var multiselect = $(this).attr("data-for-ismultiselect");

        handleClearItemImage();

		// update modal values
		$("INPUT#ajaxItemAction").val("add-item");
		$("H3#modalItemLabel").text("in "+subgroup_name);
		$("INPUT#ajaxSubgroupName").val(subgroup_name);
		$("INPUT#ajaxSubgroupID").val(subgroup_id);
		$("INPUT#ajaxItemOrdering").val(item_order);
		$("INPUT#ajaxItemIsMultiSelect").val(multiselect);
	});

	$(document).on("click", ".eq-edit-item", function () {

		var subgroup_name = $(this).attr("data-for-subgroup-name");
		var subgroup_id = $(this).attr("data-for-subgroup-id");
		var item_id = $(this).attr("data-for-item-id");
		var item_name = $(this).attr("data-for-item-name");
		var item_descr = $(this).attr("data-for-item-descr");
//		var multiselect = $(this).attr("data-for-ismultiselect");

        handleClearItemImage();
        handleShowItemImageInEditForItem(item_id);

		// update modal values
		$("INPUT#ajaxItemAction").val("edit-item");
		$("H3#modalItemLabel").text('in '+subgroup_name);
		$("INPUT#ajaxSubgroupName").val(subgroup_name);
		$("INPUT#ajaxItemID").val(item_id);
		$("INPUT#ajaxSubgroupID").val(subgroup_id);
		$("INPUT#ajaxItemName").val(item_name);
		$("INPUT#ajaxItemDescription").val(item_descr);
//		if (multiselect == 0) {
//			$("INPUT:radio[name='ajaxSubgroupIsMultiSelect'][value='0']").prop('checked', true);
//		}
//		else {
//			$("INPUT:radio[name='ajaxSubgroupIsMultiSelect'][value='1']").prop('checked', true);
//		}
	});

	$(document).on("click", ".eq-add-subgroup", function () {
		var subgroup_order = parseInt($('SPAN[data-for-subgroup-order]').last().attr('data-for-subgroup-order'));
		if (isNaN(subgroup_order)) {
			subgroup_order = 0;
		}
		else {
			// increment ordering
			subgroup_order += 1;
		}

		// update modal values
		$("INPUT#ajaxSubgroupAction").val("add-subgroup");
		$("INPUT#ajaxSubgroupOrdering").val(subgroup_order);
	});

	$(document).on("click", ".eq-edit-subgroup", function () {
		var subgroup_id = $(this).attr("data-for-subgroup-id");
		var subgroup_name = $(this).attr("data-for-subgroup-name");
		var subgroup_descr = $(this).attr("data-for-subgroup-descr");
		var multiselect = $(this).attr("data-for-ismultiselect");

		// update modal values
		$("INPUT#ajaxSubgroupAction").val("edit-subgroup");
		$("INPUT#ajaxSubgroupID").val(subgroup_id);
		$("INPUT#ajaxSubgroupName").val(subgroup_name);
		$("INPUT#ajaxSubgroupDescription").val(subgroup_descr);
		if (multiselect == 0) {
			$("INPUT:radio[name='ajaxSubgroupIsMultiSelect'][value='0']").prop('checked', true);
		}
		else {
			$("INPUT:radio[name='ajaxSubgroupIsMultiSelect'][value='1']").prop('checked', true);
		}
	});


	// ***************************
	// Cancel and cleanup
	// ***************************

    //validateAjaxGroup does not have the necessary information because managerEdit is not shown
	function cleanUpForm(formName) {
		// reset form
        if(validateAjaxGroup != undefined){
    		validateAjaxGroup.resetForm();
        }
        if(validateAjaxSubgroup != undefined){
            validateAjaxSubgroup.resetForm();
        }
        if(validateAjaxItem != undefined) {
            validateAjaxItem.resetForm();
        }
        if(validateAjaxScheduleReservations != undefined) {
            validateAjaxScheduleReservations.resetForm();
        }
		// manually remove input highlights
		$(".control-group").removeClass('success').removeClass('error');
	}

	$("#btnCancelEditGroup").click(function () {
		cleanUpForm("frmAjaxGroup");
		// hide form fields
		$("#managerEdit").addClass("hide");
		$("#managerView").removeClass("hide");
		$("#toggleGroupSettings").html('<i class="icon-white icon-pencil"></i> Edit Equipment Group');
		// hide special manager actions
		$(".manager-action").addClass("hide");
		// reset the submit button (avoid disabled state)
		$("#btnSubmitEditGroup").button('reset');
        //$(".schedule").removeClass('hide');

        //gets rid of the editing controls if shown
        if($("#item").hasClass("item-listing")){
            $(".editing-control").addClass("hide");
            $('.show-this-month').removeClass('hide');
            $('.show-this-year').removeClass('hide');
            $('.show-all').addClass('hide');
        }else{
            $(".none-at-all").removeClass('hide');
            $(".no-reserv-month").addClass('hide');
            $(".no-reserv-year").addClass('hide');
            $('.show-this-month').addClass('hide');
            $('.show-this-year').addClass('hide');
            $('.show-all').addClass('hide');
        }
        $(".show_reservation_list").removeClass('hide');
        $(".show_reservation_calendar").removeClass('hide');
	});

	$("#btnReservationCancel").click(function () {
		cleanUpForm("frmAjaxScheduleReservations");
		// hide form fields, restore button label
		$(".reservationForm").addClass("hide");
        $(".subgroupRadiosControls").addClass("hide");
        $(".subgroupCheckboxesControls").addClass("hide");
        $("#toggleReserveEquipment").html('<i class="icon-white icon-pencil"></i> Reserve Equipment');
		// strip out stored conflicts (if any exist)
		$("#show_any_conflicts").text("").hide();
		// any changes to form should hide the override button (if it exists)
		if (!$("#btnReservationOverrideConflicts").hasClass("hide")) {
			$("#btnReservationOverrideConflicts").addClass("hide");
		}
	});

	$('#btnAjaxItemCancel').click(function () {
		cleanUpForm("frmAjaxItem");
		// clear and reset form
		$("#frmAjaxItem input[type=text]").val('');
		// reset submit button (avoid disabled state)
		$("#btnAjaxItemSubmit").button('reset');
	});

	$('#btnAjaxSubgroupCancel').click(function () {
		cleanUpForm("frmAjaxSubgroup");
		// clear and reset form
		$("#frmAjaxSubgroup input[type=text]").val('');
		$("#frmAjaxSubgroup input[type=radio]").attr("checked", false);
		// reset submit button (avoid disabled state)
		$("#btnAjaxSubgroupSubmit").button('reset');
	});


	// ***************************
	// Schedule Reservation
	// ***************************

	// update date values based on Start Date
	$("#scheduleStartOnDate, #scheduleEndOnDate").change(function () {
		// update the "Repeat Ends" date so that it is never less than the Start Date
		if ($("#scheduleStartOnDate").val() > ($("#scheduleEndOnDate").val())) {
			// alert('the new date is bigger!');
			$("#scheduleEndOnDate").val($("#scheduleStartOnDate").val());
		}
	})

	// Easily set reservation for entire 24-hour period
	$("#btnAllDayEvent").click(function () {
		$("#scheduleStartTimeRaw").timepicker('setTime', '12:00 AM');
		$("#scheduleDuration").val('1D');
	});

	// Repeats Frequency: update visible fields based on user selection
	$("#scheduleFrequencyType").change(function () {
		if ($("#scheduleFrequencyType").val() == 'no_repeat') {
			// Not repeated
			$("#wrapperRepeatOptions").addClass("hide");
			$("#wrapperDoW").addClass("hide");
			$("#wrapperDoM").addClass("hide");
		}
		else if ($("#scheduleFrequencyType").val() == 'weekly') {
			// Repeat weekly
			$("#wrapperRepeatOptions").removeClass("hide");
			$("#scheduleRepeatIntervalDescription").html("weeks");
			$("#wrapperDoW").removeClass("hide");
			$("#wrapperDoM").addClass("hide");
		}
		else if ($("#scheduleFrequencyType").val() == 'monthly') {
			// Repeat monthly
			$("#wrapperRepeatOptions").removeClass("hide");
			$("#scheduleRepeatIntervalDescription").html("months");
			$("#wrapperDoW").addClass("hide");
			$("#wrapperDoM").removeClass("hide");
		}
		;
	})

	// Repeat Interval:
	// this field needs no construction

	// Repeat Ends: wire-up calendar widget
	$("#scheduleEndOnDate").datepicker({
		dateFormat: 'yy-mm-dd',
		minDate: -730,
		maxDate: +730
	}).val($.datepicker.formatDate('yy-mm-dd', new Date()));
	// Hack to make calendar icon functional
	$("#scheduleEndOnDate,#iconHackScheduleEndOnDate").click(function () {
		$("#scheduleEndOnDate").datepicker('show');
	});

	// Weekly: toggler_dow
	$(".toggler_dow").click(function (event) {
		var which = event.target.id.substr(8, 3);
		// alert("which is " + which);
		if (!$(this).hasClass('btn-info')) {
			//alert("turning on #repeat_dow_" + which);
			$("#repeat_dow_" + which).attr("value", 1);
			$("#btn_dow_" + which).addClass('btn-info');
		}
		else {
			//alert("turning off #repeat_dow_" + which);
			$("#repeat_dow_" + which).attr("value", 0);
			$("#btn_dow_" + which).removeClass('btn-info');
		}
	});

	// Monthly: toggler_dom
	$(".toggler_dom").click(function (event) {
		var which = event.target.id.substr(8, 2);
		// alert("which is " + which);
		if (!$(this).hasClass('btn-info')) {
			//alert("turning on #repeat_dom_" + which);
			$("#repeat_dom_" + which).attr("value", 1);
			$("#btn_dom_" + which).addClass('btn-info');
		}
		else {
			//alert("turning off #repeat_dom_" + which);
			$("#repeat_dom_" + which).attr("value", 0);
			$("#btn_dom_" + which).removeClass('btn-info');
		}
	});

	// Convert param to text value
	function convertDurationIntervalToText(dur) {
		var durationsJSON = '{"5M":"5 minutes", "10M":"10 minutes", "15M":"15 minutes", "20M":"20 minutes", "30M":"30 minutes", "45M":"45 minutes", "60M":"60 minutes", "90M":"90 minutes", "2H":"2 hours", "3H":"3 hours", "4H":"4 hours", "5H":"5 hours", "6H":"6 hours", "7H":"7 hours", "8H":"8 hours", "16H":"16 hours", "1D":"24 hours", "2D":"2 days", "3D":"3 days", "4D":"4 days", "5D":"5 days", "6D":"6 days", "7D":"1 week (7 days)", "14D":"2 weeks", "28D":"4 weeks"}';
		return($.parseJSON(durationsJSON)[dur]);
	}

	// Construct text string summary and final hidden values
	function getListofDates() {
		var interval = $("#scheduleRepeatInterval").val();

		var frequency = $("#scheduleFrequencyType").val();
		if (frequency == 'no_repeat') {
			frequency = 'Once';
		}
		else if (frequency == 'weekly') {
			frequency = 'Every ' + interval + ' weeks';
		}
		else if (frequency == 'monthly') {
			frequency = 'Every ' + interval + ' months';
		}

		var start_time = ' at ' + $("#scheduleStartTimeRaw").val();

		var duration = ' for ' + convertDurationIntervalToText($("#scheduleDuration").val()) + ' ';

		var end_repeat = 'until ' + $("#scheduleEndOnDate").val();

		// Determine which value to pass (DoW or DoM)
		var dates_selected = "";
		if ($("#scheduleFrequencyType").val() == 'weekly') {
			// weekly
			dates_selected = "on (";
			$("input[id*='repeat_dow_'][value='1']").each(function (i, field) {
				var text = field.name.substr(11, 3);
				switch (text) {
					case "mon":
						text = "Monday";
						break;
					case "tue":
						text = "Tuesday";
						break;
					case "wed":
						text = "Wednesday";
						break;
					case "thu":
						text = "Thursday";
						break;
					case "fri":
						text = "Friday";
						break;
					case "sat":
						text = "Saturday";
						break;
					case "sun":
						text = "Sunday";
						break;
					default:
						text = "OOPS-PROBLEM!";
						break;
				}
				dates_selected += text + ", ";
			});
			if (dates_selected.length > 10) {
				// remove trailing comma
				dates_selected = dates_selected.substr(0, dates_selected.length - 2);
			}
			dates_selected += "), ";
		}
		else if ($("#scheduleFrequencyType").val() == 'monthly') {
			// monthly
			dates_selected = "on days (";
			$("input[id*='repeat_dom_'][value='1']").each(function (i, field) {
				var text = field.name.substr(11, 2);
				dates_selected += text + ", ";
			});
			if (dates_selected.length > 10) {
				// remove trailing comma
				dates_selected = dates_selected.substr(0, dates_selected.length - 2);
			}
			dates_selected += "), ";
		}
		else {
			// no_repeat
			dates_selected = "";
		}

		// Construct the summary string
		$("#reservationSummary").text(frequency + start_time + duration + dates_selected + end_repeat);

		// Update these values, in preparation of eventual form submit
		$("#scheduleSummaryText").val($("#reservationSummary").text());

		// Convert time values from 12-hour AM/PM to 24-hour database ready format
		$("#scheduleStartTimeConverted").val(util_12To24HourFormat($("#scheduleStartTimeRaw").val()));
	}

	// Listener: click
	$("#btnAllDayEvent, .toggler_dow, .toggler_dom").click(function () {
		getListofDates();
	});
	// Listener: change
	$("#scheduleStartOnDate, #scheduleStartTimeRaw, #scheduleDuration, #scheduleFrequencyType, #scheduleRepeatInterval, #scheduleEndOnDate").change(function () {
		getListofDates();
	});

	// Listener: reservation submit button (normal)
	$("#btnReservationSubmit").click(function () {
		getListofDates();
		// display correct button loading message
		$("#btnReservationSubmit").val('just_clicked');
		$("#btnReservationOverrideConflicts").val("");
	});
	// Listener: reservation submit button (override)
	$("#btnReservationOverrideConflicts").click(function () {
		getListofDates();
		// update form value
		$("#scheduleConflictOverrideFlag").val(1);
		// display correct button loading message
		$("#btnReservationSubmit").val("");
		$("#btnReservationOverrideConflicts").val('just_clicked');
	});


    //**********************************
    // Calendar related

    $(document).on("click", "#reservations_view .show_reservation_list", function () {
        //Successfully gets the calendar day
        if($(' .schedule').hasClass("hide")){
            if($(' .calendar').hasClass('hide')){
                $(' .calendar_day').addClass('hide');
            }else{
                $(' .calendar').addClass("hide");
            }
            $(' .schedule').removeClass("hide");
        }
    });

    $(document).on("click", "#reservations_view .show_reservation_calendar", function () {
        if($(' .calendar').hasClass('hide')){
            if($(' .schedule').hasClass('hide')){
                $(' .calendar_day').addClass('hide');
            }else{
                $(' .schedule').addClass("hide");
            }
            $(' .calendar').removeClass("hide");
        }
    });

    //show daily on cell click from monthly
    $(document).on("click", "#monthly_calendar_view .calendar-day", function () {
        // create an ajax call to fetch the data for the day which was clicked, then use the results to populate the daily view, then toggle the display to show it

        //Successfully gets the calendar day
        $.ajax({
            url: 'ajax_actions/ajax_calendar_handler.php',
            dataType: 'html',
            data: {
                //day clicked on in the month
                'day_num': $(this).attr('data-daynum'),
                'month_num': $(this).attr('data-monthnum'),
				'year_num': $("#next_nav").attr('data-yearnum'),
                'eq_group_id': $("#managerView").attr('data-eid')
            }
        })
            .success(function(html){
                console.log(html);
                $(" .calendar").addClass('hide');

                //double call?
                $("#daily_calendar_view").html(html);
                $(" .calendar_day").removeClass('hide');

            });
    });

	$(document).on("click", "#daily_calendar_view .nav_elt_day_prev", function () {

			console.log($("#managerView").attr('data-eid'));
		$.ajax({
			url: 'ajax_actions/ajax_calendar_handler.php',
			dataType: 'html',
			data: {
				'prev': $(this).attr('data-prev-day'),
				'month_num': $(this).attr('data-monthnum'),
				'day_num': $(this).attr('data-daynum'),
				'year_num': $("#day-display").attr('data-yearnum'),
				'eq_group_id': $("#managerView").attr('data-eid')
			}
		})

			//replaces the current monthly view
			.success(function(html){
				$("#daily_calendar_view").html(html);
			})

	});

	$(document).on("click", "#daily_calendar_view .nav_elt_day_next", function () {

		$.ajax({
			url: 'ajax_actions/ajax_calendar_handler.php',
			dataType: 'html',
			data: {
				'next': $(this).attr('data-next-day'),
				'month_num': $(this).attr('data-monthnum'),
				'day_num': $(this).attr('data-daynum'),
				'year_num': $("#day-display").attr('data-yearnum'),
				'eq_group_id': $("#managerView").attr('data-eid')
			}
		})

			//replaces the current monthly view
			.success(function(html){
				$("#daily_calendar_view").html(html);
			})

	});

    //Show the previous month, getting the month, year from calendar elements
    $(document).on("click", "#monthly_calendar_view .nav_elt_month_prev", function () {

        $.ajax({
            url: 'ajax_actions/ajax_calendar_handler.php',
            dataType: 'html',
            data: {
                'prev': $(this).attr('data-prev'),
                'month_num': $(this).attr('data-monthnum'),
                'year_num': $(this).attr('data-yearnum'),
                'eq_group_id': $("#managerView").attr('data-eid')
			}
        })

            //replaces the current monthly view
            .success(function(html){
                $("#monthly_calendar_view").html(html);
            })

    });

    //TO IMPLEMENT: DAY TO DAY VIEWS?

    //Show the next month, getting the month, year from calendar elements
    $(document).on("click", "#monthly_calendar_view .nav_elt_month_next", function () {

        //console.log('clicked calendar NEXT'); // DEBUG

        $.ajax({
            url: 'ajax_actions/ajax_calendar_handler.php',
            dataType: 'html',
            data: {
				'next': $(this).attr('data-next'),
                'month_num': $(this).attr('data-monthnum'),
                'year_num': $(this).attr('data-yearnum'),
                'eq_group_id': $("#managerView").attr('data-eid')
            }
        })

            //replaces the current monthly view
            .success(function(html){
                console.log(html);
                $("#monthly_calendar_view").html(html);
            })
    });

    //show month on button click from daily
    $(document).on("click", "#daily_calendar_view .show_month_button", function () {
        $(" .calendar").removeClass('hide');
        $(" .calendar_day").addClass('hide');
    });

});
