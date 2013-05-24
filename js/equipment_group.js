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
			$("#btnReservationCancel").click();
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

	var validator1 = $('#frmEditGroup').validate({
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

			var url = $("#frmEditGroup").attr('action');
			var formName = "frmEditGroup";

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

	var validator2 = $('#frmAjaxItem').validate({
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
			$("#btnAjaxItemSubmit").button('loading');

			var formName = $("#frmAjaxItem").attr('name');		// get name from the form element
			var action = $('#' + formName + ' #ajaxItemAction').val();
			var subgroup_id = $('#' + formName + ' #ajaxSubgroupID').val();
			var subgroup_name = $('#' + formName + ' #ajaxSubgroupName').val();
			var item_id = $('#' + formName + ' #ajaxItemID').val();
			var item_ordering = $('#' + formName + ' #ajaxItemOrdering').val();
			var item_name = $('#' + formName + ' #ajaxItemName').val();
			var item_description = $('#' + formName + ' #ajaxItemDescription').val();
			var item_multiselect = $('#' + formName + ' #ajaxItemIsMultiSelect').val();

			$.ajax({
				type: 'GET',
				url: $("#frmAjaxItem").attr('action'),
				data: {
					ajaxVal_Action: action,
					ajaxVal_SubgroupID: subgroup_id,
					ajaxVal_SubgroupName: subgroup_name,
					ajaxVal_ItemID: item_id,
					ajaxVal_Order: item_ordering,
					ajaxVal_Name: item_name,
					ajaxVal_Description: item_description,
					ajaxVal_MultiSelect: item_multiselect
				},
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
							// update button data attributes
							$("#btn-edit-item-id-" + item_id).attr("data-for-item-name", item_name);
							$("#btn-edit-item-id-" + item_id).attr("data-for-item-descr", item_description);
							$("#btn-edit-item-id-" + item_id).attr("data-for-subgroup-name", subgroup_name);
							// update visible info
							$("span#itemid-" + item_id).html("<strong>" + item_name + ": </strong>" + item_description);
						}
					}
					else {
						// error message
						$("UL#ul-of-subgroup-" + subgroup_id + " li.manager-action").after('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Failed: No action taken</h4> A record with that same name already exists in database.</div>');
					}
				}
			});

		}
	});


	var validator3 = $('#frmAjaxSubgroup').validate({
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
			var subgroup_id = $('#' + formName + ' #ajaxSubgroupID').val();
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
				// show status
				eqrUtil_setTransientAlert('success', 'saved');
				// removed last remaining item? then show message
				if ($('#list-of-item-' + GLOBAL_confirmHandlerData).parent('UL').find('LI').length = 2) { // LI item to be removed + LI button
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
		else if (action == 'deleteSubgroup') {
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
			title: "Are you sure you want to remove this item?",
			label: "Remove Item",
			class: "btn btn-danger pull-left",
			url: "ajax_actions/ajax_delete_eq_subgroup_item.php",
			ajax_action: "deleteItem",
			ajax_id: GLOBAL_confirmHandlerData
		};

		showConfirmBox(params);
	});

	$(document).on("click", ".eq-delete-subgroup", function () {

		GLOBAL_confirmHandlerData = $(this).attr('data-for-subgroup-id');

		var params = {
			title: "Are you sure you want to remove this subgroup and all items?",
			label: "Remove Subgroup and Items",
			class: "btn btn-danger pull-left",
			url: "ajax_actions/ajax_delete_eq_subgroup.php",
			ajax_action: "deleteSubgroup",
			ajax_id: GLOBAL_confirmHandlerData
		};

		showConfirmBox(params);
	});

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

		// update modal values
		$("INPUT#ajaxItemAction").val("add-item");
		$("H3#modalItemLabel").text(subgroup_name);
		$("INPUT#ajaxSubgroupName").val(subgroup_name);
		$("INPUT#ajaxSubgroupID").val(subgroup_id);
		$("INPUT#ajaxItemOrdering").val(item_order);
		$("INPUT#ajaxItemIsMultiSelect").val(multiselect);
	});

	$(document).on("click", ".eq-edit-item", function () {
		var subgroup_name = $(this).attr("data-for-subgroup-name");
		var item_id = $(this).attr("data-for-item-id");
		var item_name = $(this).attr("data-for-item-name");
		var item_descr = $(this).attr("data-for-item-descr");
//		var multiselect = $(this).attr("data-for-ismultiselect");

		// update modal values
		$("INPUT#ajaxItemAction").val("edit-item");
		$("H3#modalItemLabel").text(subgroup_name);
		$("INPUT#ajaxItemID").val(item_id);
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

	function cleanUpForm(formName) {
		// reset form
		validator1.resetForm(this);
		validator2.resetForm(this);
		validator3.resetForm(this);
		// manually remove input highlights
		$(".control-group").removeClass('success').removeClass('error');
	}

	$("#btnCancelEditGroup").click(function () {
		cleanUpForm("frmEditGroup")
		// hide form fields
		$("#managerEdit").addClass("hide");
		$("#managerView").removeClass("hide");
		$("#toggleGroupSettings").html('<i class="icon-white icon-pencil"></i> Edit');
		// hide special manager actions
		$(".manager-action").addClass("hide");
		// reset the submit button (avoid disabled state)
		$("#btnSubmitEditGroup").button('reset');
	});

	$("#btnReservationCancel").click(function () {
		cleanUpForm("formScheduleReservations")
		// hide form fields, restore button label
		$(".reservationForm").addClass("hide");
		$("#toggleReserveEquipment").html('<i class="icon-white icon-pencil"></i> Reserve Equipment');
	});

	$('#btnAjaxItemCancel').click(function () {
		cleanUpForm("frmAjaxItem")
		// clear and reset form
		$("#frmAjaxItem input[type=text]").val('');
		// reset submit button (avoid disabled state)
		$("#btnAjaxItemSubmit").button('reset');
	});

	$('#btnAjaxSubgroupCancel').click(function () {
		cleanUpForm("frmAjaxSubgroup")
		// clear and reset form
		$("#frmAjaxSubgroup input[type=text]").val('');
		$("#frmAjaxSubgroup input[type=radio]").attr("checked", false);
		// reset submit button (avoid disabled state)
		$("#btnAjaxSubgroupSubmit").button('reset');
	});

});