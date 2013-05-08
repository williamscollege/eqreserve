$(document).ready(function () {

	$('#eq-group-add-manager-btn').click(function (evt) {
		//alert('clicked on '+$(this).attr('id'));
		$("#addUserType").val('manager');
		$("#modalFindUserUILabel").text('Add Manager');
		$('#modalAddUserUI').modal({show: 'true'});
	});

	$('#eq-group-add-consumer-btn').click(function (evt) {
		//alert('clicked on '+$(this).attr('id'));
		$("#addUserType").val('consumer');
		$("#modalFindUserUILabel").text('Add User');
		$('#modalAddUserUI').modal({show: 'true'});
	});

	$('#addUserSearchData').keypress(function (evt) {
		if (evt.which == 13) { // the return/enter key press
			//alert('enter pressed');
			evt.stopPropagation();
			event.preventDefault();
			return;
		}
		var curText = $(this).val() + String.fromCharCode(evt.which);
//		alert('TODO- handle keypress event on addUserSearchData - implement live search functionality');
        if (curText.length >= 3) {
            handleUserGroupSearchCall(curText);
        }
	});

    $('#addUserSearchData').keyup(function (evt) {
        if ((evt.which == 8) || // backspace
            (evt.which == 46)) // delete
        {
            var curText = $(this).val();
//            alert('curText is '+curText);
            if (curText.length >= 3) {
                handleUserGroupSearchCall(curText);
            }
        }
    });

    var searchTimingTag = '';
    function handleUserGroupSearchCall(searchHandlerData) {
        searchTimingTag = randomString(24);
        $('#addUserSearchResultsPreview ul').empty();
        $('#addUserSearchResultsPreview ul').append('<li><i>searching...</i></li>');
        $.ajax({
            url: 'ajax_user_and_group_search.php',
            dataType: 'json',
            data: {'action': 'find',
                   'searchTerm': searchHandlerData,
                   'timingTag':searchTimingTag
            }
        })
            .done(function (data, status, xhr) {
                //console.log(data);
                if (data.timingTag == searchTimingTag) {  // make sure only the latest search results actually update the DOM
                    if (data.status == 'success') {
                        $('#addUserSearchResultsPreview ul').empty();
                        if (data.searchResults.length > 0) {
                            for (var i = 0; i < data.searchResults.length; i++) {
                                $('#addUserSearchResultsPreview ul').append('<li>'+data.searchResults[i].sortname+'</li>');
                            }
                        }
                        else {
                            $('#addUserSearchResultsPreview ul').append('<li><i>no matches found</i></li>');
                        }
                    }
                    else {
                        var error_msg = 'bad response from server';
                        if (data.note) {
                            error_msg = data.note;
                        }
                        $('#addUserSearchResultsPreview ul').empty();
                        $('#addUserSearchResultsPreview ul').append('<li>ERROR - '+error_msg+'</li>');
                    }
                }
            })
            .fail(function (data, status, xhr) {
                eqrUtil_setTransientAlert('error', 'ERROR - could not connect to server');
            })
        ;
    }

	$('.eq-group-remove-manager-btn').click(function (evt) {
		GLOBAL_confirmHandlerData = {'perm_id': $(this).attr('data-for-id'),
			'perm_type': 'manager',
			'ent_type': $(this).attr('data-ent-type'),
			'ent_id': $(this).attr('data-ent-id')
		};
		var mgr_type = 'person';
		if ($(this).attr('data-ent-type') == 'inst_group') {
			mgr_type = 'group';
		}
		eqrUtil_launchConfirm("Are you sure you want to remove that " + mgr_type + " as a manager of this group?", handleRemovePermission);
	});

	$('#eq-group-remove-consumers-btn').click(function (evt) {
		GLOBAL_confirmHandlerData = {'perm_id': [],
			'perm_type': 'consumer'
		};
		$("#consumers-select :selected").each(function (idx, elt) {
			GLOBAL_confirmHandlerData['perm_id'].push($(this).attr('data-for-id'));
		});
		var ref_text = 'that user';
		if ($("#consumers-select :selected").length != 1) {
			ref_text = 'those users';
		}
		eqrUtil_launchConfirm("Are you sure you want to remove " + ref_text + "?", handleRemovePermission);
	});

	function handleRemovePermission() {
		eqrUtil_setTransientAlert('progress', 'saving...');
		$.ajax({
			url: 'ajax_eq_group.php',
			dataType: 'json',
			data: {'eq_group': $('#groupID').attr('value'),
				'ajaxVal_action': 'removePermission',
				'permission_ids[]': GLOBAL_confirmHandlerData.perm_id
			}
		})
			.done(function (data, status, xhr) {
				//console.log(data);
				if (data.status == 'success') {
					eqrUtil_setTransientAlert('success', '...done');
					if (GLOBAL_confirmHandlerData.perm_type == 'manager') {
						$('#remove-manager-btn-' + GLOBAL_confirmHandlerData.perm_id).remove();
					}
					else {
						for (var i = 0; i < GLOBAL_confirmHandlerData.perm_id.length; i++) {
							$('#consumer-perm-option-' + GLOBAL_confirmHandlerData.perm_id[i]).remove();
						}
					}
				}
				else {
                    var error_msg = 'bad response from server';
                    if (data.note) {
                        error_msg = data.note;
                    }
					eqrUtil_setTransientAlert('error', 'ERROR - not saved! ('+error_msg+')');
				}
			})
			.fail(function (data, status, xhr) {
				eqrUtil_setTransientAlert('error', 'ERROR - not saved!');
			})
		;
	}

	// enables/disables the remove users button depending on whether any users have been selected
	$('#consumers-select').change(function (evt) {
		if ($("#consumers-select :selected").length > 0) {
			$('#eq-group-remove-consumers-btn').removeAttr('disabled');
		}
		else {
			$('#eq-group-remove-consumers-btn').attr('disabled', 'disabled')
		}
	});
});