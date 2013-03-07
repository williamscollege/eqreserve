<?php
	$pageTitle = 'Account Management';
	require_once('head.php');
?>


This is the account management page.


<div id="account_info">
    <div class="label">Name:</div>
    <div class="value"><?php echo $USER->fname . ' ' . $USER->lname; ?></div>

    <div class="label">Username:</div>
    <div class="value"><?php echo $USER->username; ?></div>

    <div class="label">Email:</div>
    <div class="value"><?php echo $USER->email; ?></div>

    <div class="label">Advisor:</div>
    <div class="value"><?php echo $USER->advisor; ?></div>

    <div class="label">Notes (public):</div>
    <div class="value"><?php echo $USER->notes; ?></div>

    <div class="label">Institution Info:</div>
    <div class="value"><?php
		foreach ($USER->inst_groups as $ig) {
			echo $ig->name . "<br/>\n";
		}
		?></div>

    <div class="label">Equipment Groups:</div>
    <div class="value"><?php
		foreach ($USER->eq_groups as $eg) {
			echo $eg->name . "<br/>\n";
		}
		?></div>

    <div class="label">Reservations:</div>
    <div class="value"></div>
</div>


<?php
	require_once('foot.php');
?>