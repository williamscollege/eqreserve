<?php
class TimeBlock extends Db_Linked
{
    public $fields = array('time_block_id','time_block_group_id','start_time','end_time','flag_delete');
    public $primaryKeyField = 'time_block_id';    
    public $dbTable = 'time_blocks';


} 
?>