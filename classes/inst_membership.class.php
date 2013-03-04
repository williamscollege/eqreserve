<?php
require_once dirname(__FILE__) . '/db_linked.class.php';


class InstMembership extends Db_Linked
{
    public static $fields = array('inst_membership_id','user_id','inst_group_id','flag_delete');
    public static $primaryKeyField = 'inst_membership_id';    
    public static $dbTable = 'inst_memberships';
} 
?>