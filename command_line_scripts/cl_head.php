<?php

if (array_key_exists('SERVER_NAME',$_SERVER)) {
    echo 'no web access to this script';
    exit;
}

require_once('../institution.cfg.php');
require_once('../lang.cfg.php');
require_once('../util.php');

require_once('../classes/db_linked.class.php');

require_once('../classes/comm_pref.class.php');
require_once('../classes/eq_group.class.php');
require_once('../classes/eq_subgroup.class.php');
require_once('../classes/eq_item.class.php');
require_once('../classes/reservation.class.php');
require_once('../classes/schedule.class.php');
require_once('../classes/time_block.class.php');
require_once('../classes/user.class.php');

$DB = util_createDbConnection();

