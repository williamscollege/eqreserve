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
			echo "<ul id=\"displaySubgroup" . $key->eq_subgroup_id . "\" class=\"unstyled\">\n";
			if (count($key->eq_items) == 0) {
				if ($USER->flag_is_system_admin || $is_group_manager) {
					# Subgroup Title
					echo "<span data-subgroup-order=\"" . $key->ordering . "\"><strong>" . $key->name . ":</strong></span> " . $key->descr . "\n";
					echo "<li data-item-order=\"0\"><em>No items exist.</em></li>";
					# Button: Add an Item
					echo "<li class=\"manager-action hide\">";
					echo "<a href=\"#modalAddItem\" data-subgroup-id=\"" . $key->eq_subgroup_id . "\" data-is-multiselect=\"" . $key->flag_is_multi_select . "\" data-subgroup-name=\"" . $key->name . "\" data-toggle=\"modal\" class=\"btn btn-success btn-mini ajaxActionItem\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
					echo "</li>";
				}
			}
			else {
				# Subgroup Title
				if ($USER->flag_is_system_admin || $is_group_manager) {
					# delete button
					echo "<a href=\"#\" id=\"delete-subgroup-" . $key->eq_subgroup_id . "\" class=\"manager-action hide btn btn-mini btn-danger delete-subgroup-btn\" data-for-subgroup=\"" . $key->eq_subgroup_id . "\"><i class=\"icon-trash icon-white\"></i> </a> ";
				}
				echo "<span data-subgroup-order=\"" . $key->ordering . "\"><strong>" . $key->name . ":</strong></span> " . $key->descr . "\n";
				foreach ($key->eq_items as $item) {
					?>
					<li data-item-order="<?php echo $item->ordering; ?>">

						<label class="" for="item<?php echo $item->eq_item_id; ?>">
							<?php
								if ($USER->flag_is_system_admin || $is_group_manager) {
									# delete button
									echo "<a href=\"#\" id=\"delete-item-" . $item->eq_item_id . "\" class=\"manager-action hide btn btn-mini btn-danger delete-item-btn\" data-for-item=\"" . $item->eq_item_id . "\"><i class=\"icon-trash icon-white\"></i> </a> ";
								}
								if ($key->flag_is_multi_select == 0) {
									# radio: single select
									echo "<input type=\"radio\" id=\"item" . $item->eq_item_id . "\" name=\"subgroup" . $key->eq_subgroup_id . "\" class=\"reservationForm hide\" /> ";
								}
								elseif ($key->flag_is_multi_select == 1) {
									# checkbox: multiple select
									echo "<input type=\"checkbox\" id=\"item" . $item->eq_item_id . "\" name=\"\" class=\"reservationForm hide\" /> ";
								}
								echo "<strong>" . $item->name . "</strong>: " . $item->descr;
							?>
						</label>
						<!--Placeholder: Save For Later Use
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

				# Button: Add an Item
				if ($USER->flag_is_system_admin || $is_group_manager) {
					echo "<li class=\"manager-action hide\">";
					echo "<a href=\"#modalAddItem\" data-subgroup-id=\"" . $key->eq_subgroup_id . "\" data-is-multiselect=\"" . $key->flag_is_multi_select . "\" data-subgroup-name=\"" . $key->name . "\" data-toggle=\"modal\" class=\"btn btn-success btn-mini ajaxActionItem\" title=\"Add an item to this subgroup\"><i class='icon-plus icon-white'></i> Add an Item</a>";
					echo "</li>";
				}
			}
			echo "</ul>";
		} # end of foreach: eq_subgroups
		echo "</ul>";

		if ($USER->flag_is_system_admin || $is_group_manager) {
			echo "<div class=\"manager-action hide\"><br /><a href=\"#modalAddSubgroup\" data-toggle=\"modal\" class=\"btn btn-success btn-mini ajaxActionSubgroup\" title=\"Add a subgroup to this equipment group\"><i class='icon-plus icon-white'></i> Add a Subgroup</a></div>";
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
			<!-- REMOVE LATER: http://jdewit.github.io/bootstrap-timepicker/ -->
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
		<label class="control-label" for="btnSubmitReservation"></label>

		<div class="controls">
			<button type="submit" id="btnSubmitReservation" class="btn btn-success" data-loading-text="Saving...">Save</button>
			<button type="button" id="btnCancelReservation" class="btn btn-link btn-cancel">Cancel</button>
		</div>
	</div>
</form>


<!-- Modal: Item-->

<form action="ajax_add_eq_subgroup_item.php" id="frmAjaxAddItem" name="frmAjaxAddItem" method="post">
	<div id="modalAddItem" class="modal hide" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalAddItemLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			<h3 id="modalAddItemLabel"></h3>
		</div>
		<div class="modal-body">
			<div class="control-group">
				<label class="control-label" for="ajaxItemName">Name</label>

				<div class="controls">
					<input type="hidden" id="ajaxSubgroupID" name="ajaxSubgroupID" value="" />
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
			<button type="submit" id="btnAjaxSubmitAddItem" name="btnAjaxSubmitAddItem" class="btn btn-success pull-left" data-loading-text="Saving...">
				Add Item
			</button>
			<button type="reset" id="btnAjaxCancelAddItem" class="btn btn-link btn-cancel pull-left" data-dismiss="modal" aria-hidden="true">Cancel
			</button>
		</div>
	</div>
</form>


<!-- Modal: Subgroup-->
<form action="ajax_add_eq_subgroup.php" id="frmAjaxAddSubgroup" name="frmAjaxAddSubgroup" method="post">
	<div id="modalAddSubgroup" class="modal hide" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalAddSubgroupLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			<h3 id="modalAddSubgroupLabel">Add a Subgroup</h3>
		</div>
		<div class="modal-body">
			<div class="control-group">
				<label class="control-label" for="ajaxSubgroupName">Name</label>

				<div class="controls">
					<input type="hidden" id="ajaxGroupID" name="ajaxGroupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />
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
			<button type="submit" id="btnAjaxSubmitAddSubgroup" name="btnAjaxSubmitAddSubgroup" class="btn btn-success pull-left" data-loading-text="Saving...">
				Add Subgroup
			</button>
			<button type="reset" id="btnAjaxCancelAddSubgroup" class="btn btn-link btn-cancel pull-left" data-dismiss="modal" aria-hidden="true">Cancel
			</button>
		</div>
	</div>
</form>