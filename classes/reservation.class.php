<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class Reservation extends Db_Linked
{
    public static $fields = array('reservation_id','eq_item_id','time_block_group_id','flag_delete');
    public static $primaryKeyField = 'reservation_id';
    public static $dbTable = 'reservations';

	// instance attributes
    public $item='';
    public $time_block_group='';
    public $user='';
    public $time_blocks='';

    public function loadItem() {
        echo "TODO: implement Reservation->loadItem()";
        exit;
    }
    public function loadTimeBlockGroup() {
        echo "TODO: implement Reservation->loadTimeBlockGroup()";
        exit;
    }
    public function loadUser() {
        echo "TODO: implement Reservation->loadUser()";
        exit;
    }
    public function loadTimeBlocks() {
        echo "TODO: implement Reservation->loadTimeBlocks()";
        exit;
    }
}
?>