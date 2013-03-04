<?php
class Reservation extends Db_Linked
{
    public static $fields = array('reservation_id', 'eq_item_id','time_block_group_id','flag_delete');
    public static $primaryKeyField = 'reservation_id';    
    public static $dbTable = 'reservations';
} 
?>