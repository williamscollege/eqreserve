$(document).ready(function () {
	// default conditions

	// ***************************
	// Listeners
	// ***************************

	$('#eq-group-add-manager-btn').click(function (evt) {
		//alert('clicked on '+$(this).attr('id'));
		alert('hook for add manager');
		// hook for add manager UI
	});

	$('.eq-group-remove-manager-btn').click(function (evt) {
		//alert('clicked on remove manager '+$(this).attr('data-for-type')+' '+$(this).attr('data-for-id'));
		// hook for remove manager UI
		GLOBAL_confirmHandlerData = {'for_type': $(this).attr('data-for-type'), 'for_id': $(this).attr('data-for-id')};
		var mgr_type = 'person';
		if ($(this).attr('data-for-type') == 'inst_group') {
			mgr_type = 'group';
		}
		eqrUtil_launchConfirm("Are you sure you want to remove that " + mgr_type + " as a manager of this group?", handleRemoveManager);
	});
	function handleRemoveManager() {
		//eqrUtil_setTransientAlert('progress','saving...',$('#remove-manager-btn-'+GLOBAL_confirmHandlerData.for_id));
		eqrUtil_setTransientAlert('progress', 'saving...');
		alert('hook for remove manager');
		eqrUtil_setTransientAlert('success', '...manager removed');
		//eqrUtil_setTransientAlert('success','...manager removed',$('#remove-manager-btn-'+GLOBAL_confirmHandlerData.for_id));
	}

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
		// reset submit button (avoid disabled state)
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

	// Convert initial integer values to pretty text for standard output to screen
	$('#print_minDurationMinutes').text(util_minutesToWords($('#print_minDurationMinutes').text()));
	$('#print_maxDurationMinutes').text(util_minutesToWords($('#print_maxDurationMinutes').text()));
	$('#print_durationIntervalMinutes').text(util_minutesToWords($('#print_durationIntervalMinutes').text()));

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

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					ajaxVal_action: 'saveEqGroup',
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
						// error message
						$("DIV.container legend:nth-child(2)").before('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> This record was not found in the database</div>');
					}
				}
			});

		}
	});

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

			var formName = $("#frmAjaxAddItem").attr('name');		// get name from the form element
			var data1 = $('#' + formName + ' #ajaxSubgroupID').val();

			$.ajax({
				type: 'POST',
				url: $("#frmAjaxAddItem").attr('action'),
				data: {
					ajaxVal_ID: data1,
					ajaxVal_Order: $('#' + formName + ' #ajaxItemOrdering').val(),
					ajaxVal_Name: $('#' + formName + ' #ajaxItemName').val(),
					ajaxVal_Description: $('#' + formName + ' #ajaxItemDescription').val(),
					ajaxVal_MultiSelect: $('#' + formName + ' #ajaxItemIsMultiSelect').val()
				},
				dataType: 'html',
				success: function (data) {
					// hide and reset form
					$("#btnAjaxCancelAddItem").click();

					if (data) {
						// remove error messages
						$('DIV.alert-error').remove();

						// remove text item: 'No items exist.'
						$("UL#ul-of-subgroup-" + data1 + " li").remove(":contains('No items exist.')");

						// update element with resultant ajax data
						$("UL#ul-of-subgroup-" + data1 + " li.manager-action").before(data);
					}
					else {
						// error message
						$("UL#ul-of-subgroup-" + data1 + " li.manager-action").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
					}
				}
			});

		}
	});

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
			// alert('url=' + url + '\n' + 'formName=' + formName + '\n' + 'data1=' + data1 + '\n' + 'data2=' + data2);

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					ajaxVal_ID: $('#' + formName + ' #ajaxGroupID').val(),
					ajaxVal_Order: $('#' + formName + ' #ajaxSubgroupOrdering').val(),
					ajaxVal_Name: $('#' + formName + ' #ajaxSubgroupName').val(),
					ajaxVal_Description: $('#' + formName + ' #ajaxSubgroupDescription').val(),
					ajaxVal_MultiSelect: $('#' + formName + ' input:radio[name=ajaxSubgroupIsMultiSelect]:checked').val()
				},
				dataType: 'html',
				success: function (data) {
					// hide and reset form
					$("#btnAjaxCancelAddSubgroup").click();

					if (data) {
						// remove error messages
						$('DIV.alert-error').remove();

						// update element with resultant ajax data
						$("UL#displayAllSubgroups").append(data);
					}
					else {
						// error message
						$("UL#displayAllSubgroups").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
					}
				}
			});

		}
	});

	$(document).on("click", ".delete-item-btn", function () {
		// The 'on' method binds the document with the function handler's actions for selected items ('.ajaxActionItem') to the 'click' event
		// This is useful for binding dynamically generated DOM elements to the document

		GLOBAL_confirmHandlerData = $(this).attr('data-for-item');

		var params = {
			title: "Are you sure you want to remove this item?",
			label: "Remove Item",
			class: "btn btn-danger pull-left",
			url: "ajax_delete_eq_subgroup_item.php",
			ajax_action: "deleteItem",
			ajax_id: GLOBAL_confirmHandlerData
		};

		showConfirmBox(params);
	});

	$(document).on("click", ".delete-subgroup-btn", function () {

		GLOBAL_confirmHandlerData = $(this).attr('data-for-subgroup');

		var params = {
			title: "Are you sure you want to remove this subgroup and all items?",
			label: "Remove Subgroup and Items",
			class: "btn btn-danger pull-left",
			url: "ajax_delete_eq_subgroup.php",
			ajax_action: "deleteSubgroup",
			ajax_id: GLOBAL_confirmHandlerData
		};

		showConfirmBox(params);
	});

	$(".delete-schedule-btn").click(function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-schedule');
		var confirmText = "<p>Are you sure you want to remove that schedule of reservations?</p>\n";
		eqrUtil_launchConfirm(confirmText, handleDeleteSchedule);
	});
	function handleDeleteSchedule() {
		eqrUtil_setTransientAlert('progress', 'saving...', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
		$.ajax({
			url: 'ajax_schedule.php',
			dataType: 'json',
			data: {'schedule': GLOBAL_confirmHandlerData,
				'scheduleAction': 'deleteSchedule',
				'actionVal': GLOBAL_confirmHandlerData
			}
		})
			.done(function (data, status, xhr) {
				if (data.status == 'success') {
					eqrUtil_setTransientAlert('success', 'saved', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
					$('#list-of-schedule-' + GLOBAL_confirmHandlerData).remove();
				}
				else {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
				}
			})
			.fail(function (data, status, xhr) {
				eqrUtil_setTransientAlert('error', 'ERROR - not saved!', $('#list-of-schedule-' + GLOBAL_confirmHandlerData));
			})
		;
	}


	function showConfirmBox(ary) {
		eqrUtil_setTransientAlert('progress', 'saving...');

		bootbox.dialog(ary['title'],
			[
				{
					"label": ary['label'],
					"class": ary['class'],
					"callback": function () {
						$.ajax({
							type: 'GET',
							url: ary['url'],
							data: {
								'ajaxVal_Action': ary['ajax_action'],
								'ajaxVal_ID': ary['ajax_id']
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
		if (action == 'deleteItem') {
			if (ret) {
				eqrUtil_setTransientAlert('success', 'saved');
				// remove element
				$('#list-of-item-' + GLOBAL_confirmHandlerData).remove();
			}
			else {
				// error message
				$("#list-of-item-" + GLOBAL_confirmHandlerData).after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> No matching record was found in the database.</div>');
			}
		}
		else if (action == 'deleteSubgroup') {
			if (ret) {
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


	function cleanUpForm(formName) {
		// reset form
		validator1.resetForm(this);
		validator2.resetForm(this);
		validator3.resetForm(this);
		// manually remove input highlights
		$(".control-group").removeClass('success').removeClass('error');
	}


	// Modals
	$(document).on("click", "UL#displayAllSubgroups .ajaxActionItem", function () {
		// The 'on' method binds the document with the function handler's actions for selected items ('.ajaxActionItem') to the 'click' event
		// This is useful for binding dynamically generated DOM elements to the document

		// pass values to modal
		var n = $(this).attr("data-subgroup-name");
		var i = $(this).attr("data-subgroup-id");
		var o = parseInt($(this).parent('LI').prev('LI').attr("data-item-order"));
		if (isNaN(o)) {
			// i.e. user deleted all items
			o = 1;
		}
		else {
			// item: increment ordering value
			o += 1;
		}
		// fetch subgroup characteristic 'IsMultiSelect' for modal hidden input
		var m = $(this).attr("data-is-multiselect");

		// update modal values
		$("H3#modalAddItemLabel").text(n);
		$("INPUT#ajaxSubgroupID").val(i);
		$("INPUT#ajaxItemOrdering").val(o);
		$("INPUT#ajaxItemIsMultiSelect").val(m);
	});

	$('a.ajaxActionSubgroup').click(function () {
		// pass values to modal
		var o = parseInt($('SPAN[data-subgroup-order]').last().attr('data-subgroup-order'));
		if (isNaN(o)) {
			o = 0;
		}
		else {
			// subgroup: increment ordering value
			o += 1;
		}

		// update modal values
		$("INPUT#ajaxSubgroupOrdering").val(o);
	});

	$('#btnAjaxCancelAddItem').click(function () {
		cleanUpForm("frmAjaxAddItem")
		// clear and reset form
		$("#frmAjaxAddItem input[type=text], #frmAjaxAddItem textarea").val("");
		$("#frmAjaxAddItem input[type=radio]").attr("checked", false);
		// reset submit button (avoid disabled state)
		$("#btnAjaxSubmitAddItem").button('reset');
	});

	$('#btnAjaxCancelAddSubgroup').click(function () {
		cleanUpForm("frmAjaxAddSubgroup")
		// clear and reset form
		$("#frmAjaxAddSubgroup input[type=text], #frmAjaxAddSubgroup textarea").val("");
		$("#frmAjaxAddSubgroup input[type=radio]").attr("checked", false);
		// reset submit button (avoid disabled state)
		$("#btnAjaxSubmitAddSubgroup").button('reset');
	});

});