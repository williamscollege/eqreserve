<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class EqGroup extends Db_Linked
{
    public static $fields = array('eq_group_id','name','descr',
                           'start_minute','min_duration_minutes','max_duration_minutes','duration_chunk_minutes',
                           'flag_delete');
    public static $primaryKeyField = 'eq_group_id';    
    public static $dbTable = 'eq_groups';


    public function getEqGroups($user) {

		if ($_SESSION['isAuthenticated'] == true) {

/*		
	    $fields = array('eq_group_id','name','descr',
	                           'start_minute','min_duration_minutes','max_duration_minutes','duration_chunk_minutes',
	                           'flag_delete');
			# continue: session is authenticated

			# test data (real data would call the DB and build eq_groups array based on rowcount of groups)

	    	$this->eq_groups = array();
	    	$this->eq_groups[0]['name'] = TESTGROUP1_NAME;
	    	$this->eq_groups[0]['role'] = TESTGROUP1_ROLE;
	    	$this->eq_groups[1]['name'] = TESTGROUP2_NAME;
	    	$this->eq_groups[1]['role'] = TESTGROUP2_ROLE;
	    	$this->eq_groups[2]['name'] = TESTGROUP3_NAME;
	    	$this->eq_groups[2]['role'] = TESTGROUP3_ROLE;

			return $fields;
*/	    	
			return true;
		} else {
			# exit: session is not authenticated
			return false;
		}
	}


}
?>
