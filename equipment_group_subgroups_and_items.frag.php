<form action="reservation.php" class="form-horizontal" id="formScheduleReservations" name="formScheduleReservations" method="post">
	<input type="hidden" id="reservationGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />

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
									echo "<input type=\"radio\" id=\"item-" . $item->eq_item_id . "\" name=\"subgroup-" . $key->eq_subgroup_id . "\" class=\"reservationForm hide\" /> ";
								}
								elseif ($key->flag_is_multi_select == 1) {
									# checkbox: multiple select
									echo "<input type=\"checkbox\" id=\"item-" . $item->eq_item_id . "\" name=\"\" class=\"reservationForm hide\" /> ";
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
		<h4>Schedule Reservation</h4>
		<label class="control-label" for="reservationStartDate">Start Date</label>

		<div class="controls">
			<div class="input-append">
				<input type="text" id="reservationStartDate" class="input-small" maxlength="12" />
				<span id="iconHackForceStartDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
			</div>
			&nbsp;&nbsp;Time
			<div class="input-append bootstrap-timepicker">
				<input id="reservationStartTime" type="text" class="input-small" value="" maxlength="8" />
				<span class="add-on"><i class="icon-time"></i></span>
			</div>
		</div>
	</div>
	<div class="control-group reservationForm hide">
		<label class="control-label" for="reservationEndDate">End Date</label>

		<div class="controls">
			<div class="input-append">
				<input type="text" id="reservationEndDate" class="input-small" maxlength="12" />
				<span id="iconHackForceEndDate" class="add-on cursorPointer"><i class="icon-calendar"></i></span>
			</div>
			&nbsp;&nbsp;Time
			<div class="input-append bootstrap-timepicker">
				<input id="reservationEndTime" type="text" class="input-small" value="" maxlength="8" />
				<span class="add-on"><i class="icon-time"></i></span>
			</div>
		</div>
	</div>


	<div class="control-group reservationForm hide">
		<label class="control-label" for="repeatReservation">Repeat Reservation?</label>

		<div class="controls">
			<input type="radio" checked="true" id="repeatReservation" name="repeatReservation" value="not_repeated"> not repeated |
			<input type="radio" id="repeatReservation" name="repeatReservation" value="daily"> daily |
			<input type="radio" id="repeatReservation" name="repeatReservation" value="weekly"> weekly |
			<input type="radio" id="repeatReservation" name="repeatReservation" value="monthly"> monthly
			<br />(repeat rates cause addition ui elements to appear to support specifying days of week, or month, and an end date)
		</div>
	</div>

	<?php
		if ($USER->flag_is_system_admin || $is_group_manager) {
			?>
			<div class="control-group reservationForm hide">
				<label class="control-label" for="managerReservation">Maintenance Period?</label>

				<div class="controls">
					<input type="checkbox" id="managerReservation" name="managerReservation"> Check box to indicate this is a maintenance or non-use
					period
				</div>
			</div>
		<?php
		}
	?>

	<div class="control-group reservationForm hide">
		<label class="control-label" for="btnReservationSubmit"></label>

		<div class="controls">
			<button type="submit" id="btnReservationSubmit" class="btn btn-success" data-loading-text="Saving...">Save</button>
			<button type="button" id="btnReservationCancel" class="btn btn-link btn-cancel">Cancel</button>
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
