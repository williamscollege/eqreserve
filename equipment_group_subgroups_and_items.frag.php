<form action="reservation.php" class="form-horizontal" id="formScheduleReservations" name="formScheduleReservations" method="post">
<input type="hidden" id="reservationGroupID" name="reservationGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />
<input type="hidden" id="reservationSummaryText" name="reservationSummaryText" value="" />
<legend class="pull-left row-fluid">Reserve Equipment
	<a href="#" id="toggleReserveEquipment" class="btn btn-medium btn-primary"><i class="icon-white icon-pencil"></i> Reserve Equipment</a></legend>

<?php
	# Load EQSubgroups
	$Requested_EqGroup->loadEqSubgroups();
	//			util_prePrintR($Requested_EqGroup);

	echo "<ul id=\"displayAllSubgroups\" class=\"unstyled\">\n";
	foreach ($Requested_EqGroup->eq_subgroups as $key) {

		# Subgroup Items
		$key->loadEqItems();

		# Items
		echo "<ul id=\"ul-of-subgroup-" . $key->eq_subgroup_id . "\" class=\"unstyled\">\n";
		if (count($key->eq_items) == 0) {
			# Subgroup Title
			if ($USER->flag_is_system_admin || $is_group_manager) {
				# button: edit subgroup
				echo "<a id=\"btn-edit-subgroup-id-" . $key->eq_subgroup_id . "\" href=\"#modalSubgroup\" data-toggle=\"modal\" data-for-subgroup-id=\"" . $key->eq_subgroup_id . "\" data-for-ismultiselect=\"" . $key->flag_is_multi_select . "\" data-for-subgroup-name=\"" . $key->name . "\" data-for-subgroup-descr=\"" . $key->descr . "\" class=\"manager-action hide btn btn-mini btn-primary eq-edit-subgroup\" title=\"Edit\"><i class=\"icon-pencil icon-white\"></i> </a> ";
				# button: delete subgroup
				echo "<a class=\"manager-action hide btn btn-mini btn-danger eq-delete-subgroup\" data-for-subgroup-id=\"" . $key->eq_subgroup_id . "\" title=\"Delete\"><i class=\"icon-trash icon-white\"></i> </a> ";
				echo "<span id=\"subgroupid-" . $key->eq_subgroup_id . "\" data-for-subgroup-order=\"" . $key->ordering . "\"><strong>" . $key->name . ": </strong>" . $key->descr . "</span>\n";
				echo "<li class=\"manager-action hide\">";
				echo "<span class=\"noItemsExist\"><em>No items exist.</em><br /></span>";
				# button: add item
				echo "<a href=\"#modalItem\" data-toggle=\"modal\" data-for-subgroup-id=\"" . $key->eq_subgroup_id . "\" data-for-ismultiselect=\"" . $key->flag_is_multi_select . "\" data-for-subgroup-name=\"" . $key->name . "\" class=\"btn btn-success btn-mini eq-add-item\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
				echo "</li>";
			}
		}
		else {
			# Subgroup Title
			if ($USER->flag_is_system_admin || $is_group_manager) {
				# button: edit subgroup
				echo "<a id=\"btn-edit-subgroup-id-" . $key->eq_subgroup_id . "\" href=\"#modalSubgroup\" data-toggle=\"modal\" data-for-subgroup-id=\"" . $key->eq_subgroup_id . "\" data-for-ismultiselect=\"" . $key->flag_is_multi_select . "\" data-for-subgroup-name=\"" . $key->name . "\" data-for-subgroup-descr=\"" . $key->descr . "\" class=\"manager-action hide btn btn-mini btn-primary eq-edit-subgroup\" title=\"Edit\"><i class=\"icon-pencil icon-white\"></i> </a> ";
				# button: delete subgroup
				echo "<a class=\"manager-action hide btn btn-mini btn-danger eq-delete-subgroup\" data-for-subgroup-id=\"" . $key->eq_subgroup_id . "\" title=\"Delete\"><i class=\"icon-trash icon-white\"></i> </a> ";
			}
			echo "<span id=\"subgroupid-" . $key->eq_subgroup_id . "\" data-for-subgroup-order=\"" . $key->ordering . "\"><strong>" . $key->name . ": </strong>" . $key->descr . "</span>\n";
			foreach ($key->eq_items as $item) {
				?>
				<li id="list-of-item-<?php echo $item->eq_item_id; ?>" data-for-item-order="<?php echo $item->ordering; ?>">

					<label class="" for="item-<?php echo $item->eq_item_id; ?>">
						<?php
							if ($USER->flag_is_system_admin || $is_group_manager) {
								# button: edit item
								echo "<a id=\"btn-edit-item-id-" . $item->eq_item_id . "\" href=\"#modalItem\" data-toggle=\"modal\" data-for-subgroup-name=\"" . $key->name . "\" data-for-item-id=\"" . $item->eq_item_id . "\" data-for-item-name=\"" . $item->name . "\" data-for-item-descr=\"" . $item->descr . "\" class=\"manager-action hide btn btn-mini btn-primary eq-edit-item\" title=\"Edit\"><i class=\"icon-pencil icon-white\"></i> </a> ";
								# button: delete item
								echo "<a id=\"delete-item-" . $item->eq_item_id . "\" class=\"manager-action hide btn btn-mini btn-danger eq-delete-item\" data-for-item-id=\"" . $item->eq_item_id . "\" title=\"Delete\"><i class=\"icon-trash icon-white\"></i> </a> ";
							}
							if ($key->flag_is_multi_select == 0) {
								# radio: single select
								echo "<input type=\"radio\" id=\"item-" . $item->eq_item_id . "\" name=\"subgroup-" . $key->eq_subgroup_id . "\" value=\"" . $item->eq_item_id . "\"  class=\"reservationForm hide\" /> ";
							}
							elseif ($key->flag_is_multi_select == 1) {
								# checkbox: multiple select
								echo "<input type=\"checkbox\" id=\"item-" . $item->eq_item_id . "\" name=\"item-" . $item->eq_item_id . "\" value=\"" . $item->eq_item_id . "\"  class=\"reservationForm hide\" /> ";
							}
							echo "<span id=\"itemid-" . $item->eq_item_id . "\"><strong>" . $item->name . ": </strong>" . $item->descr . "</span>\n";
						?>
					</label>
					<!--Placeholder: Save For Later Use: maybe utilize HighCharts bar graph?
					<div class="controls">
						<div class="progress span8">
							<div class="bar bar-info" style="width: 35%;"></div>
							<div class="bar bar-warning" style="width: 20%;"></div>
							<div class="bar bar-success" style="width: 35%;"></div>
							<div class="bar bar-danger" style="width: 10%;"></div>
						</div>
					</div>-->
				</li>
			<?php
			} # end of foreach: eq_items

			if ($USER->flag_is_system_admin || $is_group_manager) {
				echo "<li class=\"manager-action hide\">";
				echo "<span class=\"noItemsExist hide\"><em>No items exist.</em><br /></span>";
				# button: add item
				echo "<a href=\"#modalItem\" data-toggle=\"modal\" data-for-subgroup-id=\"" . $key->eq_subgroup_id . "\" data-for-ismultiselect=\"" . $key->flag_is_multi_select . "\" data-for-subgroup-name=\"" . $key->name . "\" class=\"btn btn-success btn-mini eq-add-item\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
				echo "</li>";
			}
		}
		echo "</ul>";
	} # end of foreach: eq_subgroups
	echo "</ul>";

	if ($USER->flag_is_system_admin || $is_group_manager) {
		echo "<div class=\"manager-action hide\"><br /><a href=\"#modalSubgroup\" data-toggle=\"modal\" class=\"btn btn-success btn-mini eq-add-subgroup\" title=\"Add a subgroup to this equipment group\"><i class='icon-plus icon-white'></i> Add a Subgroup</a></div>";
	}
?>


<div class="control-group reservationForm hide">
	<br />
	<h4>Schedule Reservation</h4>
	<!--	<legend class="pull-left row-fluid">Schedule Reservation</legend>-->
	<div class="">
		<div class="input-append" title="Start date">
			<input type="text" id="reservationStartDate" name="reservationStartDate" class="input-small" maxlength="12" />
			<span id="iconHackReservationStartDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
		</div>
		<div id="wrapperReservationStartTime" class="input-append bootstrap-timepicker" title="Start time">
			<input type="text" id="reservationStartTime" name="reservationStartTime" class="input-small" value="" maxlength="8" />
			<span class="add-on"><i class="icon-time"></i></span>
		</div>
		to
		<div id="wrapperReservationEndTime" class="input-append bootstrap-timepicker" title="End time">
			<input type="text" id="reservationEndTime" name="reservationEndTime" class="input-small" value="" maxlength="8" />
			<span class="add-on"><i class="icon-time"></i></span>
		</div>
		<div class="input-append" title="End date">
			<input type="text" id="reservationEndDate" name="reservationEndDate" class="input-small" maxlength="12" />
			<span id="iconHackReservationEndDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
		</div>
	</div>
</div>

<!--start google riff-->
<div class="control-group reservationForm hide">
	<div class="control-group">
		<label class="control-label" for="isAllDayEvent">All day:</label>

		<div class="controls" title="Is this an all day event?">
			<input type="checkbox" class="checkbox" id="isAllDayEvent" name="isAllDayEvent" />
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="repeatFrequencyType">Repeats:</label>

		<div class="controls" title="Repeat Frequency">
			<select id="repeatFrequencyType" name="repeatFrequencyType" class="input-xlarge">
				<option value="no_repeat" title="Only on this one date">Only on</option>
				<option value="weekly" title="Repeat on days of the week">Repeat on days of the week</option>
				<option value="monthly" title="Repeat on days of the month">Repeat on days of the month</option>
			</select>
		</div>
	</div>

	<div id="wrapperRepeatOptions" class="hide">
		<div class="control-group">
			<label class="control-label" for="repeatInterval">Repeat every:</label>

			<div class="controls" title="Repeat every">
				<select id="repeatInterval" name="repeatInterval" class="input-mini">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="21">21</option>
					<option value="22">22</option>
					<option value="23">23</option>
					<option value="24">24</option>
					<option value="25">25</option>
					<option value="26">26</option>
					<option value="27">27</option>
					<option value="28">28</option>
					<option value="29">29</option>
					<option value="30">30</option>
				</select>
				<span id="repeatIntervalDescription">weeks</span>
			</div>
		</div>

		<div id="wrapperDoW" class="control-group">
			<label class="control-label" for="DoW">Repeat on:</label>

			<div class="controls">
				<div id="DoW">
					<span class="pull-left row-fluid" style="padding: 4px">
						<input type="hidden" name="repeat_dow_sun" id="repeat_dow_sun" value="0" />
						<input type="hidden" name="repeat_dow_mon" id="repeat_dow_mon" value="0" />
						<input type="hidden" name="repeat_dow_tue" id="repeat_dow_tue" value="0" />
						<input type="hidden" name="repeat_dow_wed" id="repeat_dow_wed" value="0" />
						<input type="hidden" name="repeat_dow_thu" id="repeat_dow_thu" value="0" />
						<input type="hidden" name="repeat_dow_fri" id="repeat_dow_fri" value="0" />
						<input type="hidden" name="repeat_dow_sat" id="repeat_dow_sat" value="0" />
						<input type="button" id="btn_dow_mon" value="MON" class="btn-mini toggler_dow" title="Repeat on Monday" />&nbsp;
						<input type="button" id="btn_dow_tue" value="TUE" class="btn-mini toggler_dow" title="Repeat on Tuesday" />&nbsp;
						<input type="button" id="btn_dow_wed" value="WED" class="btn-mini toggler_dow" title="Repeat on Wednesday" />&nbsp;
						<input type="button" id="btn_dow_thu" value="THU" class="btn-mini toggler_dow" title="Repeat on Thursday" />&nbsp;
						<input type="button" id="btn_dow_fri" value="FRI" class="btn-mini toggler_dow" title="Repeat on Friday" />&nbsp;
					</span>
					<span class="pull-left row-fluid" style="padding: 4px">
						<input type="button" id="btn_dow_sat" value="SAT" class="btn-mini toggler_dow" title="Repeat on Saturday" />&nbsp;
						<input type="button" id="btn_dow_sun" value="SUN" class="btn-mini toggler_dow" title="Repeat on Sunday" />&nbsp;
					</span>
				</div>
			</div>
		</div>


		<div id="wrapperDoM" class="control-group">
			<label class="control-label" for="DoW">Repeat on:</label>

			<div class="controls">
				<div id="DoW">
					<span class="pull-left row-fluid" style="padding: 4px">
						<input type="hidden" name="repeat_dom_1" id="repeat_dom_1" value="0" />
						<input type="button" id="btn_dom_1" value="01" class="btn-mini toggler_dom" title="Repeat on 1st day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_2" id="repeat_dom_2" value="0" />
						<input type="button" id="btn_dom_2" value="02" class="btn-mini toggler_dom" title="Repeat on 2nd day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_3" id="repeat_dom_3" value="0" />
						<input type="button" id="btn_dom_3" value="03" class="btn-mini toggler_dom" title="Repeat on 3rd day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_4" id="repeat_dom_4" value="0" />
						<input type="button" id="btn_dom_4" value="04" class="btn-mini toggler_dom" title="Repeat on 4th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_5" id="repeat_dom_5" value="0" />
						<input type="button" id="btn_dom_5" value="05" class="btn-mini toggler_dom" title="Repeat on 5th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_6" id="repeat_dom_6" value="0" />
						<input type="button" id="btn_dom_6" value="06" class="btn-mini toggler_dom" title="Repeat on 6th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_7" id="repeat_dom_7" value="0" />
						<input type="button" id="btn_dom_7" value="07" class="btn-mini toggler_dom" title="Repeat on 7th day of month" />&nbsp;
					</span>
					<span class="pull-left row-fluid" style="padding: 4px">
						<input type="hidden" name="repeat_dom_8" id="repeat_dom_8" value="0" />
						<input type="button" id="btn_dom_8" value="08" class="btn-mini toggler_dom" title="Repeat on 8th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_9" id="repeat_dom_9" value="0" />
						<input type="button" id="btn_dom_9" value="09" class="btn-mini toggler_dom" title="Repeat on 9th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_10" id="repeat_dom_10" value="0" />
						<input type="button" id="btn_dom_10" value="10" class="btn-mini toggler_dom" title="Repeat on 10th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_11" id="repeat_dom_11" value="0" />
						<input type="button" id="btn_dom_11" value="11" class="btn-mini toggler_dom" title="Repeat on 11th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_12" id="repeat_dom_12" value="0" />
						<input type="button" id="btn_dom_12" value="12" class="btn-mini toggler_dom" title="Repeat on 12th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_13" id="repeat_dom_13" value="0" />
						<input type="button" id="btn_dom_13" value="13" class="btn-mini toggler_dom" title="Repeat on 13th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_14" id="repeat_dom_14" value="0" />
						<input type="button" id="btn_dom_14" value="14" class="btn-mini toggler_dom" title="Repeat on 14th day of month" />&nbsp;
					</span>
					<span class="pull-left row-fluid" style="padding: 4px">
						<input type="hidden" name="repeat_dom_15" id="repeat_dom_15" value="0" />
						<input type="button" id="btn_dom_15" value="15" class="btn-mini toggler_dom" title="Repeat on 15th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_16" id="repeat_dom_16" value="0" />
						<input type="button" id="btn_dom_16" value="16" class="btn-mini toggler_dom" title="Repeat on 16th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_17" id="repeat_dom_17" value="0" />
						<input type="button" id="btn_dom_17" value="17" class="btn-mini toggler_dom" title="Repeat on 17th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_18" id="repeat_dom_18" value="0" />
						<input type="button" id="btn_dom_18" value="18" class="btn-mini toggler_dom" title="Repeat on 18th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_19" id="repeat_dom_19" value="0" />
						<input type="button" id="btn_dom_19" value="19" class="btn-mini toggler_dom" title="Repeat on 19th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_20" id="repeat_dom_20" value="0" />
						<input type="button" id="btn_dom_20" value="20" class="btn-mini toggler_dom" title="Repeat on 20th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_21" id="repeat_dom_21" value="0" />
						<input type="button" id="btn_dom_21" value="21" class="btn-mini toggler_dom" title="Repeat on 21st day of month" />&nbsp;
					</span>
					<span class="pull-left row-fluid" style="padding: 4px">
						<input type="hidden" name="repeat_dom_22" id="repeat_dom_22" value="0" />
						<input type="button" id="btn_dom_22" value="22" class="btn-mini toggler_dom" title="Repeat on 22nd day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_23" id="repeat_dom_23" value="0" />
						<input type="button" id="btn_dom_23" value="23" class="btn-mini toggler_dom" title="Repeat on 23rd day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_24" id="repeat_dom_24" value="0" />
						<input type="button" id="btn_dom_24" value="24" class="btn-mini toggler_dom" title="Repeat on 24th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_25" id="repeat_dom_25" value="0" />
						<input type="button" id="btn_dom_25" value="25" class="btn-mini toggler_dom" title="Repeat on 25th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_26" id="repeat_dom_26" value="0" />
						<input type="button" id="btn_dom_26" value="26" class="btn-mini toggler_dom" title="Repeat on 26th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_27" id="repeat_dom_27" value="0" />
						<input type="button" id="btn_dom_27" value="27" class="btn-mini toggler_dom" title="Repeat on 27th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_28" id="repeat_dom_28" value="0" />
						<input type="button" id="btn_dom_28" value="28" class="btn-mini toggler_dom" title="Repeat on 28th day of month" />&nbsp;
					</span>
					<span class="pull-left row-fluid" style="padding: 4px">
						<input type="hidden" name="repeat_dom_29" id="repeat_dom_29" value="0" />
						<input type="button" id="btn_dom_29" value="29" class="btn-mini toggler_dom" title="Repeat on 29th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_30" id="repeat_dom_30" value="0" />
						<input type="button" id="btn_dom_30" value="30" class="btn-mini toggler_dom" title="Repeat on 30th day of month" />&nbsp;
						<input type="hidden" name="repeat_dom_31" id="repeat_dom_31" value="0" />
						<input type="button" id="btn_dom_31" value="31" class="btn-mini toggler_dom" title="Repeat on 31st day of month" />&nbsp;
					</span>
				</div>
			</div>
		</div>


		<div class="control-group">
			<label class="control-label" for="">Ends:</label>

			<div class="controls">
				<label for="repeatEndType_1" title="Ends after a number of occurrences">
					<input type="radio" id="repeatEndType_1" name="repeatEndType" checked="checked" value="end_on_quantity" /> After
					<input type="text" id="repeatEndOnQuantity" name="repeatEndOnQuantity" size="3" value="1" class="input-mini" maxlength="3" />
					occurrences
				</label>
				<label for="repeatEndType_2" title="Ends on a specified date">
					<input type="radio" id="repeatEndType_2" name="repeatEndType" value="end_on_date" /> On
					<div class="input-append">
						<input type="text" id="repeatEndOnDate" name="repeatEndOnDate" class="input-small" maxlength="12" class="input-small" required="required" />
						<span id="iconHackRepeatEndOnDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
					</div>
				</label>
			</div>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="ajaxSubgroupIsMultiSelect">Summary:</label>

		<div class="controls">
			<span id="reservationSummary">Once.</span>
		</div>
	</div>
</div>
<!--end google riff-->



<?php
	if ($USER->flag_is_system_admin || $is_group_manager) {
		?>
		<div class="control-group reservationForm hide">
			<label class="control-label" for="reservationType">Maintenance Period?</label>

			<div class="controls">
				<input type="checkbox" id="reservationType" name="reservationType"> Check box to indicate this is a maintenance or non-use
				period
			</div>
		</div>
	<?php
	}
?>

<div class="control-group reservationForm hide">
	<label class="control-label" for="btnReservationSubmit"></label>

	<div class="controls">
		<button type="submit" id="btnReservationSubmit" name="btnReservationSubmit" class="btn btn-success" data-loading-text="Saving...">Save</button>
		<button type="button" id="btnReservationCancel" name="btnReservationCancel" class="btn btn-link btn-cancel">Cancel</button>
	</div>
</div>
</form>


<!-- MODAL: Add/Edit Subgroup-->
<form action="ajax_actions/ajax_eq_subgroup.php" id="frmAjaxSubgroup" name="frmAjaxSubgroup" method="post">
	<div id="modalSubgroup" class="modal hide" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalSubgroupLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			<h3 id="modalSubgroupLabel">Subgroup</h3>
		</div>
		<div class="modal-body">
			<div class="control-group">
				<label class="control-label" for="ajaxSubgroupName">Name</label>

				<div class="controls">
					<input type="hidden" id="ajaxSubgroupAction" name="ajaxSubgroupAction" value="" />
					<input type="hidden" id="ajaxGroupID" name="ajaxGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />
					<input type="hidden" id="ajaxSubgroupID" name="ajaxSubgroupID" value="" />
					<input type="hidden" id="ajaxSubgroupOrdering" name="ajaxSubgroupOrdering" value="" />
					<input type="text" id="ajaxSubgroupName" name="ajaxSubgroupName" class="input-large" value="" placeholder="Name of Subgroup" maxlength="200" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ajaxSubgroupDescription">Description</label>

				<div class="controls">
					<input type="text" id="ajaxSubgroupDescription" class="input-xlarge" name="ajaxSubgroupDescription" value="" placeholder="Description of Subgroup" maxlength="200" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ajaxSubgroupIsMultiSelect">Allow one or many items in this subgroup to be selected</label>

				<div class="controls">
					<input type="radio" id="ajaxSubgroupSingle" name="ajaxSubgroupIsMultiSelect" value="0" /> Single Select (radio buttons)<br />
					<input type="radio" id="ajaxSubgroupMulti" name="ajaxSubgroupIsMultiSelect" value="1" /> Multiple Select (checkboxes)
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" id="btnAjaxSubgroupSubmit" name="btnAjaxSubgroupSubmit" class="btn btn-success pull-left" data-loading-text="Saving...">
				Save Subgroup
			</button>
			<button type="reset" id="btnAjaxSubgroupCancel" class="btn btn-link btn-cancel pull-left" data-dismiss="modal" aria-hidden="true">Cancel
			</button>
		</div>
	</div>
</form>


<!-- MODAL: Add/Edit Item-->
<form action="ajax_actions/ajax_eq_subgroup_item.php" id="frmAjaxItem" name="frmAjaxItem" method="post">
	<div id="modalItem" class="modal hide" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalItemLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			<h3 id="modalItemLabel"></h3>
		</div>
		<div class="modal-body">
			<div class="control-group">
				<label class="control-label" for="ajaxItemName">Name</label>

				<div class="controls">
					<input type="hidden" id="ajaxItemAction" name="ajaxItemAction" value="" />
					<input type="hidden" id="ajaxSubgroupName" name="ajaxSubgroupName" value="" />
					<input type="hidden" id="ajaxSubgroupID" name="ajaxSubgroupID" value="" />
					<input type="hidden" id="ajaxItemID" name="ajaxItemID" value="" />
					<input type="hidden" id="ajaxItemOrdering" name="ajaxItemOrdering" value="" />
					<input type="hidden" id="ajaxItemIsMultiSelect" name="ajaxItemIsMultiSelect" value="" />
					<input type="text" id="ajaxItemName" name="ajaxItemName" class="input-large" value="" placeholder="Name of Item" maxlength="200" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ajaxItemDescription">Description</label>

				<div class="controls">
					<input type="text" id="ajaxItemDescription" class="input-xlarge" name="ajaxItemDescription" value="" placeholder="Description of Item" maxlength="200" />
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" id="btnAjaxItemSubmit" name="btnAjaxItemSubmit" class="btn btn-success pull-left" data-loading-text="Saving...">
				Save Item
			</button>
			<button type="reset" id="btnAjaxItemCancel" class="btn btn-link btn-cancel pull-left" data-dismiss="modal" aria-hidden="true">Cancel
			</button>
		</div>
	</div>
</form>
