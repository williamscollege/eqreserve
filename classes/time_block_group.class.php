<?php
class TimeBlockGroup extends Db_Linked
{
    public static $fields = array('time_block_group_id','type','user_id','notes','flag_delete');
    public static $primaryKeyField = 'time_block_group_id';    
    public static $dbTable = 'time_block_groups';

    public $user;
    public $time_blocks;
    public $reservations;

    public static function cmp($a,$b) {
        // first by user
        // if users are equal, then by manager consumer
        // if manager / consumer are equal, then by earliest time block
        if ($a->user_id == $b->user_id) {
            if ($a->type == $b->type) {
                if (! $a->time_blocks) {
                    $a->loadTimeBlocks();
                }
                if (! $b->time_blocks) {
                    $b->loadTimeBlocks();
                }
                return TimeBlock::cmp($a->time_blocks[0],$b->time_blocks[0]);
            }
            return (($a->type == 'manager') && ($b->type == 'consumer')) ? -1 : 1;
        }
        if (! $a->user) {
            $a->loadUser();
        }
        if (! $b->user) {
            $b->loadUser();
        }
        return User::cmp($a->user,$b->user);
    }

    // instance methods

    public function loadUser() {
        $this->user = User::getOneFromDb(['user_id'=>$this->user_id],$this->dbConnection);
    }

    public function loadTimeBlocks() {
        $this->time_blocks = TimeBlock::getAllFromDb(['time_block_group_id'=>$this->time_block_group_id],$this->dbConnection);
        usort($this->time_blocks,"TimeBlock::cmp");
    }

    public function loadReservations() {
        $this->reservations = Reservation::getAllFromDb(['time_block_group_id'=>$this->time_block_group_id],$this->dbConnection);
        usort($this->reservations,"Reservation::cmp");
    }
}
?>