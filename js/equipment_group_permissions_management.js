$(document).ready(function () {

	$('#eq-group-add-manager-btn').click(function (evt) {
		//alert('clicked on '+$(this).attr('id'));
		$("#addUserType").val('manager');
		$("#modalFindUserUILabel").text('Add Manager');
        doShowAddUserSearchModal();
	});

	$('#eq-group-add-consumer-btn').click(function (evt) {
		//alert('clicked on '+$(this).attr('id'));
		$("#addUserType").val('consumer');
		$("#modalFindUserUILabel").text('Add User');
        doShowAddUserSearchModal();
	});
    function doShowAddUserSearchModal() {
        $("#addUserSearchData").val('');
        $('#addUserSearchResultsPreview ul').empty();
        $('#addUserSearchResultsPreview ul').append('<li class="text-info"><i>type above to start a search</i></li>');
        $('#modalAddUserUI').modal({show: 'true'});
        $("#addUserSearchData").focus();
    }

    var searchDelayTimer = 0; // used to delay submission of search until the user has stopped typing

	$('#addUserSearchData').keypress(function (evt) {
		if (evt.which == 13) { // the return/enter key press
			//alert('enter pressed');
			evt.stopPropagation();
            evt.preventDefault();
			return;
		}
		var curText = $(this).val() + String.fromCharCode(evt.which);
        if (curText.length >= 3) {
            clearTimeout(searchDelayTimer);
            searchDelayTimer = setTimeout(handleUserGroupSearchCall,400,curText);
        }
	});

    $('#addUserSearchData').keyup(function (evt) {
        if ((evt.which == 8) || // backspace
            (evt.which == 46)) // delete
        {
            var curText = $(this).val();
//            alert('curText is '+curText);
            if (curText.length >= 3) {
                clearTimeout(searchDelayTimer);
                searchDelayTimer = setTimeout(handleUserGroupSearchCall,400,curText);
            }
        }
    });

    var searchTimingTag = '';
    function handleUserGroupSearchCall(searchHandlerData) {
//        alert('made search handler call');
        searchTimingTag = randomString(24);
        $('#addUserSearchResultsPreview ul').empty();
        $('#addUserSearchResultsPreview ul').append('<li class="text-success"><i>searching...</i></li>');
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
                                var item;
//console.log(data.searchResults[i]);
                                if (data.searchResults[i].hasOwnProperty('user_id')) {
                                    item = makeSearchResItemForUser(data.searchResults[i]);
                                }
                                else {
                                    item = makeSearchResItemForInstGroup(data.searchResults[i]);
                                }
//                                $('#addUserSearchResultsPreview ul').append('<li>'+data.searchResults[i].sortname+'</li>');
                                $('#addUserSearchResultsPreview ul').append(item);
                            }
                        }
                        else {
                            $('#addUserSearchResultsPreview ul').append('<li class="text-warning"><i>no matches found</i></li>');
                        }
                    }
                    else {
                        var error_msg = 'bad response from server';
                        if (data.note) {
                            error_msg = data.note;
                        }
                        $('#addUserSearchResultsPreview ul').empty();
                        $('#addUserSearchResultsPreview ul').append('<li class="text-error">ERROR - '+error_msg+'</li>');
                    }
                }
            })
            .fail(function (data, status, xhr) {
                eqrUtil_setTransientAlert('error', 'ERROR - could not connect to server');
            })
        ;
    }

    function makeSearchResItemForUser(u) {
//console.log(u);
        var res = '<li>';
        res += '<div class="searchResultAction pull-left">';
        if (u.flag_is_banned == '1') {
            res += '<button class="btn-danger btn-small" title="user has been banned!" disabled="disabled"><i class="icon-minus-sign icon-white"></i></button>';
        }
        else {
            res += '<button class="btn-success btn-small" title="add this" data-add-type="user" data-add-id="'+ u.user_id+'" data-username="'+ u.username+'"><i class="icon-plus-sign icon-white"></i></button>';
        }
        res += '</div>';
        res += '<div class="searchResultData  pull-left"><div>';
        if (u.flag_is_banned == '1') {
            res += '<span class="text-error"><i class="icon-user"></i> <b>BANNED</b> '+u.fname+' '+u.lname+' <i>('+u.username+')</i></span>';
        }
        else {
            res += '<i class="icon-user"></i> '+u.fname+' '+u.lname+' <i>('+u.username+')</i>';
        }
        res += '</div></div><br clear="all"/>';
        res += '</li>';
        return res;
    }

    function makeSearchResItemForInstGroup(ig) {
        var res = '<li>';
        res += '<div class="searchResultAction pull-left">' +
            '<button class="btn-success btn-small" title="add this" data-add-type="inst_group" data-add-id="'+ ig.inst_group_id+'" data-username="'+ ig.name+'"><i class="icon-plus-sign icon-white"></i></button>' +
            '</div>';
        res += '<div class="searchResultData  pull-left">' +
            '<div>['+ig.name+']</div>' +
            '</div><br clear="all"/>';
        res += '</li>';
        return res;
    }

    $(document).on('click','.searchResultAction .btn-success',function(evt){
        evt.stopPropagation();
        evt.preventDefault();
        var cached_this = this;
        // ajax call to ajax_eq_group with action=add_permission, perm_type=manager or consumer, entity_type=data add type, entity_id=data add id, username=data username, eq group id
        // transient alert for 'saving...'
        eqrUtil_setTransientAlert('progress', 'saving...',$('#addUserSearchData'));
        $.ajax({
            url: 'ajax_eq_group.php',
            dataType: 'json',
            data: {'eq_group': $('#groupID').attr('value'),
                'ajaxVal_action': 'addPermission',
                'permission_type': $("#addUserType").val(),
                'entity_type':$(this).attr('data-add-type'),
                'entity_id':$(this).attr('data-add-id'),
                'username':$(this).attr('data-username')
            }
        })
            .done(function (data, status, xhr) {
                console.log(data);
                if (data.status == 'success') {
                    // on success, update DOM: add to text list of managers, add button to remove manager controls if approp, add option to consumers select list if approp
                    //    expecting back in ajax results: status, permission_id, text/info needed for creation of above DOM elements
                    eqrUtil_setTransientAlert('success', '...done',$('#addUserSearchData'));
                    $(cached_this).parent().parent().remove();
                    var display_name = '';
                    var display_shortname = '';
                    if (data.entity_type == 'user') {
                        display_name = data.name+' ('+data.email+')';
                        display_shortname = data.name;
                    }
                    else {
                        display_name = '['+data.name+']';
                        display_shortname = '['+data.name+']';
                    }
                    if (data.added_type == 'manager') {
                        // handle adding control (button) and display (li) elts for managers
                        $('#managersControlSet').append(' <button type="button" id="remove-manager-btn-'+data.permission_id+'" class="btn btn-inverse btn-small eq-group-remove-manager-btn" title="'+display_name+'" data-ent-type="'+data.entity_type+'" data-ent-id="'+data.entity_id+'" data-for-id="'+data.permission_id+'"> '+((data.entity_type == 'user') ? '<i class="icon-user  icon-white"></i> ' : '') + display_name+'<i class="icon-remove icon-white"></i></button>');
                        $('#displayListOfManagers').append(' <li id="display-manager-'+data.entity_type+'-'+data.entity_id+'">'+((data.entity_type == 'user') ? '<i class="icon-user"></i> ' : '')+display_shortname+'</li>');
                    }
                    else {
                        // handle addition of control elts (options) for consumer
                        $('#consumers-select').append('<option id="consumer-perm-option-'+data.permission_id+'" title="'+display_name+'" data-ent-type="'+data.entity_type+'" data-ent-id="'+data.entity_id+'" data-for-id="'+data.permission_id+'">'+display_name+'</option>');
                    }
                }
                else {
                    var error_msg = 'bad response from server';
                    if (data.note) {
                        error_msg = data.note;
                    }
                    eqrUtil_setTransientAlert('error', 'ERROR - not saved! ('+error_msg+')',$('#addUserSearchData'));
                }
            })
            .fail(function (data, status, xhr) {
                eqrUtil_setTransientAlert('error', 'ERROR - not saved!',$('#addUserSearchData'));
            })
        ;

    });

    $(document).on('click','.eq-group-remove-manager-btn',function(evt){
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
                        var btn_id = '#remove-manager-btn-' + GLOBAL_confirmHandlerData.perm_id;
                        $('#display-manager-'+$(btn_id).attr('data-ent-type')+'-'+$(btn_id).attr('data-ent-id')).remove();
						$(btn_id).remove();
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