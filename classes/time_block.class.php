<?php
class TimeBlock extends Db_Linked
{
    public static $fields = array('time_block_id','time_block_group_id','start_time','end_time','flag_delete');
    public static $primaryKeyField = 'time_block_id';    
    public static $dbTable = 'time_blocks';

    
} 
?>