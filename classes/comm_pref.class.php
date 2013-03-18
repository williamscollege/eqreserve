<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class CommPref extends Db_Linked
{
    public static $fields = array('comm_pref_id','user_id','eq_group_id',
                           'flag_alert_on_upcoming_reservation','flag_contact_on_reserve_create','flag_contact_on_reserve_cancel');
    public static $primaryKeyField = 'comm_pref_id';    
    public static $dbTable = 'comm_prefs';


} 
?>