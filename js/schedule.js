var ajax_url = 'ajax_actions/ajax_schedule.php';

$(document).ready(function () {
	// Toggle view vs edit
	$("#toggleEditMode").click(function () {
		// toggle form or plain-text
		$(".editing-control").toggleClass("hide");
		$(".view-control").toggleClass("hide");

		// toggle button label
		if ($("#toggleEditMode").attr('data-cur-mode') == 'view') {
			$("#toggleEditMode").html('<i class="icon-white icon-ok"></i> View');
			$("#toggleEditMode").attr('data-cur-mode', 'edit');
		}
		else {
			$("#toggleEditMode").html('<i class="icon-white icon-pencil"></i> Edit');
			$("#toggleEditMode").attr('data-cur-mode', 'view');
		}
	});

	$("#sched-notes").blur(function () {
		// check for change from original/previous notes, do the update if it differs
		if ($('#sched-notes-view').html() != $("#sched-notes").val()) {
			eqrUtil_setTransientAlert('progress', 'saving...');
			$.ajax({
				url: ajax_url,
				dataType: 'json',
				data: {'schedule': scheduleId,
					'scheduleAction': 'updateNotes',
					'actionVal': $("#sched-notes").val()
				}
			})
				.done(function (data, status, xhr) {
					if (data.status == 'success') {
						eqrUtil_setTransientAlert('success', 'saved');
						$('#sched-notes-view').html($("#sched-notes").val());
					}
					else {
						eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
					}
				})
				.fail(function (data, status, xhr) {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				})
//                    .always(function(d,s,x){
//                        for (p in d) {
//                            if (d.hasOwnProperty(p)) {
//                                console.log(p+': '+d[p]);
//                            }
//                        }
//                    })
			;
		}
	});

	$("#sched-is-manager-btn").click(function () {
		var curType = $("#resv-current-type").html();
		var schedNewType = (curType == 'management') ? 'consumer' : 'manager';
		eqrUtil_setTransientAlert('progress', 'saving...');
		$.ajax({
			url: ajax_url,
			dataType: 'json',
			data: {'schedule': scheduleId,
				'scheduleAction': 'updateType',
				'actionVal': schedNewType
			}
		})
			.done(function (data, status, xhr) {
				if (data.status == 'success') {
					eqrUtil_setTransientAlert('success', 'saved');
					// toggle form or plain-text
					if (curType == 'management') {
						$("#resv-current-type").html('regular')
						$("#resv-other-type").html('management')
						$('#sched-is-manager-view-text').html('This is a regular user schedule');
					}
					else {
						$("#resv-current-type").html('management')
						$("#resv-other-type").html('regular')
						$('#sched-is-manager-view-text').html('This is a management schedule!');
					}
					$('#sched-is-manager-btn i.signifier').toggleClass('hide');
					$('#sched-is-manager-btn').toggleClass('btn-warning');
					$('#sched-is-manager-btn').toggleClass('btn-info');
					$('#sched-is-manager-view').toggleClass('text-warning');
					$('#sched-is-manager-view').toggleClass('text-info');
					$('#sched-is-manager-view i').toggleClass('icon-user');
					$('#sched-is-manager-view i').toggleClass('icon-wrench');
				}
				else {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				}
			})
			.fail(function (data, status, xhr) {
				eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
			})
		;

	});

	$(".delete-time-block-btn").click(function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-time-block');
		var confirmText = "<p>Are you sure you want to remove that time block from this schedule of reservations?</p>\n";
		if ($("#time_blocks li").length == 1) {
			confirmText += "<p><b>NOTE:</b> since that is the only time block removing it will remove this entire schedule of reservations!</p>";
		}
		eqrUtil_launchConfirm(confirmText, handleDeleteTimeBlock);
	});
	function handleDeleteTimeBlock() {
		eqrUtil_setTransientAlert('progress', 'saving...');
		$.ajax({
			url: ajax_url,
			dataType: 'json',
			data: {'schedule': scheduleId,
				'scheduleAction': 'deleteTimeBlock',
				'actionVal': GLOBAL_confirmHandlerData
			}
		})
			.done(function (data, status, xhr) {
				if (data.status == 'success') {
					eqrUtil_setTransientAlert('success', 'saved');
					// if there's only one item in the list, jump to user acct page
					// else remove the relevant list item
					if ($('ul#time_blocks > li').length <= 1) {
						eqrUtil_setTransientAlert('success', 'schedule deleted! Redirecting...');
						window.location.href = headToOnScheduleGone;
					}
					else {
						$('#list-of-time-block-' + GLOBAL_confirmHandlerData).remove();
					}
				}
				else {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				}
			})
			.fail(function (data, status, xhr) {
				eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
			})
		;
	}

	$(".delete-reservation-btn").click(function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-reservation');
		var confirmText = "<p>Are you sure you want to remove that item from this schedule of reservations?</p>\n";
		if ($("#reservations li").length == 1) {
			confirmText += "<p><b>NOTE:</b> since that is the only item removing it will remove this entire schedule of reservations!</p>";
		}
		eqrUtil_launchConfirm(confirmText, handleDeleteReservation);
	});
	function handleDeleteReservation() {
		eqrUtil_setTransientAlert('progress', 'saving...');
		$.ajax({
			url: ajax_url,
			dataType: 'json',
			data: {'schedule': scheduleId,
				'scheduleAction': 'deleteReservation',
				'actionVal': GLOBAL_confirmHandlerData
			}
		})
			.done(function (data, status, xhr) {
				if (data.status == 'success') {
					eqrUtil_setTransientAlert('success', 'saved');
					// if there's only one item in the list, jump to user acct page
					// else remove the relevant list item
					if ($('ul#reservations > li').length <= 1) {
						eqrUtil_setTransientAlert('success', 'schedule deleted! Redirecting...');
						window.location.href = headToOnScheduleGone;
					}
					else {
						$('#list-of-reservation-' + GLOBAL_confirmHandlerData).remove();
					}
				}
				else {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				}
			})
			.fail(function (data, status, xhr) {
				eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
			})
		;
	}

	$("#deleteEntireScheduleBtn").click(function () {
		GLOBAL_confirmHandlerData = $(this).attr('data-for-schedule');
		eqrUtil_launchConfirm("<p>Are you sure you want to delete this entire schedule of reservations?</p>\n", handleDeleteSchedule);
	});
	function handleDeleteSchedule() {
		eqrUtil_setTransientAlert('progress', 'saving...');
		$.ajax({
			url: ajax_url,
			dataType: 'json',
			data: {'schedule': scheduleId,
				'scheduleAction': 'deleteSchedule',
				'actionVal': GLOBAL_confirmHandlerData
			}
		})
			.done(function (data, status, xhr) {
				if (data.status == 'success') {
//                            eqrUtil_setTransientAlert('success','saved');
					eqrUtil_setTransientAlert('success', 'schedule deleted! Redirecting...');
					window.location.href = headToOnScheduleGone;
				}
				else {
					eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
				}
			})
			.fail(function (data, status, xhr) {
				eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
			})
		;
	}

});