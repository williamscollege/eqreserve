<?php

	# admin or manager: is allowed to edit fields
	if ($USER->flag_is_system_admin || $is_group_manager) {

		$manager_permission_data  = array();
		$consumer_permission_data = array();
		foreach ($Requested_EqGroup->permissions as $p) {
			$display_text = '';
			if ($p->entity_type == 'user') {
				$u = User::getOneFromDb(['user_id' => $p->entity_id], $DB);
				if ($u->matchesDb) {
					$display_text = $u->fname . ' ' . $u->lname . ' (' . $u->email . ')';
				}
			}
			else {
				$g = InstGroup::getOneFromDb(['inst_group_id' => $p->entity_id], $DB);
				if ($g->matchesDb) {
					$display_text = '[' . $g->name . ']';
				}
			}
			if ($display_text) {
				if ($p->role_id == 1) {
					array_push($manager_permission_data, ['perm_id' => $p->permission_id, 'ent_type' => $p->entity_type, 'ent_id' => $p->entity_id, 'display_text' => $display_text]);
				}
				else {
					array_push($consumer_permission_data, ['perm_id' => $p->permission_id, 'ent_type' => $p->entity_type, 'ent_id' => $p->entity_id, 'display_text' => $display_text]);
				}
			}
		}

		?>
		<legend class="pull-left row-fluid"><?php echo $Requested_EqGroup->name; ?>
			<a href="#" id="toggleGroupSettings" class="btn btn-medium btn-primary"><i class="icon-white icon-pencil"></i> Edit</a></legend>

		<div id="managerEdit" class="hide">
			<form action="ajax_actions/ajax_eq_group.php" class="form-horizontal" id="frmAjaxGroup" name="frmAjaxGroup" method="post">
				<input type="hidden" id="groupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />

				<div class="control-group">
					<label class="control-label" for="groupName">Name</label>

					<div class="controls">
						<input type="text" id="groupName" class="input-large" name="groupName" value="<?php echo $Requested_EqGroup->name; ?>" placeholder="Name of group" maxlength="200" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="groupDescription">Description</label>

					<div class="controls">
						<textarea rows="3" id="groupDescription" class="input-large" name="groupDescription" placeholder="Description of group"><?php echo $Requested_EqGroup->descr; ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="groupManagers">Managed by</label>

					<div class="controls" id="managersControlSet">
						<button id="eq-group-add-manager-btn" type="button" class="btn btn-success btn-small" title="Add Manager">
							<i class="icon-plus-sign icon-white"></i> Add
							Manager <i class="icon-plus-sign icon-white"></i></button>
						<?php
							// TODO: sort appropriately
							echo join(" ",
								array_map(function ($pd) {
									return "<button type=\"button\" id=\"remove-manager-btn-" . $pd['perm_id'] . "\" class=\"btn btn-inverse btn-small eq-group-remove-manager-btn\" title=\"" . $pd['display_text'] . "\" data-ent-type=\"" . $pd['ent_type'] . "\" data-ent-id=\"" . $pd['ent_id'] . "\" data-for-id=\"" . $pd['perm_id'] . "\">" . (($pd['ent_type'] == 'user') ? '<i class="icon-user  icon-white"></i> ' : '') . $pd['display_text'] . " <i class=\"icon-remove icon-white\"></i></button>";
								}, $manager_permission_data)
							);
						?>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="groupManagers">Reservable by</label>

					<div class="controls">
						<i>use CTRL and/or SHIFT to select more than one</i></i><br />
						<select name="consumers-select" id="consumers-select" class="user-select" size="12" multiple="multiple">
							<?php
								// TODO: sort appropriately
								echo join(" ",
									array_map(function ($pd) {
										// icon does not show in options		return "<option id=\"consumer-perm-option-" . $pd['perm_id'] . "\" title=\"" . $pd['display_text'] . "\" data-ent-type=\"" . $pd['ent_type'] . "\" data-ent-id=\"" . $pd['ent_id'] . "\" data-for-id=\"" . $pd['perm_id'] . "\">" . (($pd['ent_type'] == 'user') ? '<i class="icon-user  icon-white"></i> ' : '') . $pd['display_text'] . "</option>\n";
										return "<option id=\"consumer-perm-option-" . $pd['perm_id'] . "\" title=\"" . $pd['display_text'] . "\" data-ent-type=\"" . $pd['ent_type'] . "\" data-ent-id=\"" . $pd['ent_id'] . "\" data-for-id=\"" . $pd['perm_id'] . "\">" . $pd['display_text'] . "</option>\n";
									}, $consumer_permission_data)
								);
							?>
						</select><br /><br />
						<button type="button" id="eq-group-add-consumer-btn" class="btn btn-success btn-small" title="Add Group User">
							<i class="icon-plus-sign icon-white"></i> Add <i class="icon-plus-sign icon-white"></i></button>
						<button type="button" id="eq-group-remove-consumers-btn" class="btn btn-danger btn-small" title="Remove Selected" disabled="disabled">
							<i class="icon-minus-sign icon-white"></i> Remove Selected <i class="icon-minus-sign icon-white"></i></button>
					</div>
				</div>

				<legend>Reservation Rules</legend>

				<div class="control-group">
					<label class="control-label" for="goStartMinute">Start time (minutes)</label>

					<div class="controls">
						<?php
							$defaultStartMinute = [
								""           => "Select or Edit",
								"00"         => "hourly (00)",
								"0,30"       => "half hours (00,30)",
								"0,15,30,45" => "quarter hours (00,15,30,45)"
							];
						?>
						<select id="goStartMinute" class="span2">
							<?php
								foreach ($defaultStartMinute as $key => $val) {
									echo "<option value=\"$key\">$val</option>\n";
								}
							?>
						</select>
						<i class="icon-arrow-right"></i>
						<input type="text" id="startMinute" class="input-medium" name="startMinute" value="<?php echo $Requested_EqGroup->start_minute; ?>" placeholder="Minutes (with commas)" maxlength="200" />
						Reservations must start and end on one of these minutes of the hour.
					</div>
				</div>
				<?php
					$defaultDuration = [
						""    => "Select or Edit",
						15    => "15 minutes",
						30    => "30 minutes",
						45    => "45 minutes",
						60    => "1 hour",
						120   => "2 hours",
						240   => "4 hours",
						480   => "8 hours",
						960   => "16 hours",
						1440  => "24 hours",
						2880  => "2 days",
						10080 => "1 week",
						20160 => "2 weeks",
						40320 => "4 weeks",
						80640 => "8 weeks"
					];
				?>
				<div class="control-group">
					<label class="control-label" for="goMinDurationMinutes">Min duration (minutes)</label>

					<div class="controls">
						<select id="goMinDurationMinutes" class="span2">
							<?php
								foreach ($defaultDuration as $key => $val) {
									echo "<option value=\"$key\">$val</option>\n";
								}
							?>
						</select>
						<i class="icon-arrow-right"></i>
						<input type="text" id="minDurationMinutes" class="input-mini" name="minDurationMinutes" value="<?php echo $Requested_EqGroup->min_duration_minutes; ?>" placeholder="Duration" maxlength="6" />
						The minimum length of time that can be reserved.
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="goMaxDurationMinutes">Max duration (minutes)</label>

					<div class="controls">
						<select id="goMaxDurationMinutes" class="span2">
							<?php
								foreach ($defaultDuration as $key => $val) {
									echo "<option value=\"$key\">$val</option>\n";
								}
							?>
						</select>
						<i class="icon-arrow-right"></i>
						<input type="text" id="maxDurationMinutes" class="input-mini" name="maxDurationMinutes" value="<?php echo $Requested_EqGroup->max_duration_minutes; ?>" placeholder="Duration" maxlength="6" />
						The maximum length of time that can be reserved.
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="goDurationIntervalMinutes">Duration unit (minutes)</label>

					<div class="controls">
						<select id="goDurationIntervalMinutes" class="span2">
							<?php
								foreach ($defaultDuration as $key => $val) {
									echo "<option value=\"$key\">$val</option>\n";
								}
							?>
						</select>
						<i class="icon-arrow-right"></i>
						<input type="text" id="durationIntervalMinutes" class="input-mini" name="durationIntervalMinutes" value="<?php echo $Requested_EqGroup->duration_chunk_minutes; ?>" placeholder="Duration" maxlength="6" />
						The interval unit duration of time that can be reserved.
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="btnSubmitEditGroup"></label>

					<div class="controls">
						<button type="submit" id="btnSubmitEditGroup" class="btn btn-success" data-loading-text="Saving...">Save</button>
						<button type="button" id="btnCancelEditGroup" class="btn btn-link btn-cancel">Cancel</button>
					</div>
				</div>
			</form>
		</div>

		<div id="modalAddUserUI" class="modal hide" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalAddUserUILabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				<h3 id="modalFindUserUILabel">Add User or Group</h3>
			</div>
			<div class="modal-body">
				<form>
					<input type="hidden" id="addUserType" value="" />

					<div class="control-group">
						<p>Find user or group</p>
						<input type="text" id="addUserSearchData" name="addUserSearchData" class="input-large" value="" placeholder="search" maxlength="200" title="search by name, user name, course name, or course id" />
						<i class="muted">searches as you type after 3+ characters</i>
					</div>
					<div id="addUserSearchResultsPreview" class="control-group addUserSearchResultsPreview">
						<ul class="unstyled userGroupSearchResultList">
							<li class="text-info"><i>type above to start a search</i></li>
						</ul>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" id="btnAjaxCancelAddUser" class="btn btn-link btn-cancel pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	<?php
	}

	# Show this to all authenticated users
	echo "<div id=\"managerView\">\n";
	//	echo "Group: <span id=\"print_groupName\">" . $Requested_EqGroup->name . "</span><br />\n";
	echo "<span id=\"print_groupDescription\">" . $Requested_EqGroup->descr . "</span><br /><br />\n";
	echo "Managed by: <ul id=\"displayListOfManagers\" class=\"inline\">";
	echo join("\n",
		array_map(function ($m) {
				if (get_class($m) == 'User') {
					return "<li id=\"display-manager-user-" . $m->user_id . "\"><i class=\"icon-user\"></i> $m->fname $m->lname</li>";
				}
				return "<li id=\"display-manager-inst_group-" . $m->inst_group_id . "\">[$m->name]</li>";
			},
			$Requested_EqGroup->managers)
	);
	echo "</ul>\n";
	//	echo "<br />\n";
	//	echo "<legend>Reservation Rules</legend>\n";
	//	echo "Start times <span class=\"label label-inverse\" title=\"Reservations must start and end on one of these minutes of the hour\"><span id=\"print_startMinute\">" . $Requested_EqGroup->start_minute . "</span> minutes</span><br />\n";
	//	echo "Min duration <span class=\"label label-inverse\" title=\"The minimum length of time that can be reserved\"><span id=\"print_minDurationMinutes\">" . $Requested_EqGroup->min_duration_minutes . "</span></span><br />\n";
	//	echo "Max duration <span class=\"label label-inverse\" title=\"The maximum length of time that can be reserved\"><span id=\"print_maxDurationMinutes\">" . $Requested_EqGroup->max_duration_minutes . "</span></span><br />\n";
	//	echo "Duration unit <span class=\"label label-inverse\" title=\"The time reserved must be an even multiple of this - this is the smallest about by which a reservation duration may be altered\"><span id=\"print_durationIntervalMinutes\">" . $Requested_EqGroup->duration_chunk_minutes . "</span></span><br />\n";
	echo "</div>";

?>