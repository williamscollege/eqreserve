<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class Reservation extends Db_Linked
{
    public static $fields = array('reservation_id','eq_item_id','time_block_group_id','flag_delete');
    public static $primaryKeyField = 'reservation_id';
    public static $dbTable = 'reservations';

	// instance attributes
    public $eq_item='';
    public $time_block_group='';
    public $user='';
    public $time_blocks='';

    public static function cmp($a,$b) {
        if (! $a->eq_item) {
            $a->loadEqItem();
        }
        if (! $b->eq_item) {
            $b->loadEqItem();
        }
        return EqItem::cmp($a->eq_item,$b->eq_item);
    }

    public function loadEqItem() {
        $this->eq_item = EqItem::getOneFromDb(['eq_item_id'=>$this->eq_item_id],$this->dbConnection);
    }

    public function loadTimeBlockGroup() {
        $this->time_block_group = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>$this->time_block_group_id],$this->dbConnection);
    }

    /*
     * NOTE: potentially serious inefficiencies here as we're doing an 'extra' db call to load the time block group
     * so that we can get the user and list of time blocks. We gout get aroudn this by overriding the getOne and getAll
     * static functions to do more complex fetching and object building. However, that's a messy and error-prone enough
     * process that we're just living with this inefficiency untiland unless it becomes clear that it's a problem on
     * the useability end (as opposed to just an aesthetics of design / coding issue).
     */

    public function loadUser() {
        if (! $this->time_block_group) {
            $this->loadTimeBlockGroup();
        }
        $this->user = User::getOneFromDb(['user_id'=>$this->time_block_group->user_id],$this->dbConnection);
    }
    public function loadTimeBlocks() {
        if (! $this->time_block_group) {
            $this->loadTimeBlockGroup();
        }
        $this->time_blocks = TimeBlock::getAllFromDb(['time_block_group_id'=>$this->time_block_group_id],$this->dbConnection);
        usort($this->time_blocks,"TimeBlock::cmp");
    }
}
?>