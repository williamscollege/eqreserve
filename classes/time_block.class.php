<?php
class TimeBlock extends Db_Linked
{
    public static $fields = array('time_block_id','time_block_group_id','start_time','end_time','flag_delete');
    public static $primaryKeyField = 'time_block_id';    
    public static $dbTable = 'time_blocks';

    public $time_block_group='';
    public $user='';
    public $reservations='';

    public static function cmp($a,$b) {
        if ($a->start_time == $b->start_time) {
            return 0;
        }
        return ($a->start_time < $b->start_time) ? -1 : 1;
    }

    public function loadTimeBlockGroup() {
        $this->time_block_group = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>$this->time_block_group_id,'flag_delete'=>false],$this->dbConnection);
    }

    public function loadUser() {
        if (! $this->time_block_group) {
            $this->loadTimeBlockGroup();
        }
        $this->user = User::getOneFromDb(['user_id'=>$this->time_block_group->user_id,'flag_delete'=>false],$this->dbConnection);
    }

    public function loadReservations() {
        if (! $this->time_block_group) {
            $this->loadTimeBlockGroup();
        }
        $this->reservations = Reservation::getAllFromDb(['time_block_group_id'=>$this->time_block_group->time_block_group_id,'flag_delete'=>false],$this->dbConnection);
        usort($this->reservations,"Reservation::cmp");
    }
}
?>