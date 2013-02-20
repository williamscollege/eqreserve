<?php
class eq_groups extends Db_Linked
{


    public function get_EQ_Groups($user) {
    	$this->eq_groups	= array();

		if ($_SESSION['isAuthenticated'] == true) {
			# continue: session is authenticated

			# test data (real data would call the DB and build eq_groups array based on rowcount of groups)
	    	$this->eq_groups = array();
	    	$this->eq_groups[0]['name'] = TESTGROUP1_NAME;
	    	$this->eq_groups[0]['role'] = TESTGROUP1_ROLE;
	    	$this->eq_groups[1]['name'] = TESTGROUP2_NAME;
	    	$this->eq_groups[1]['role'] = TESTGROUP2_ROLE;
	    	$this->eq_groups[2]['name'] = TESTGROUP3_NAME;
	    	$this->eq_groups[2]['role'] = TESTGROUP3_ROLE;
	    	
			return true;
		} else {
			# exit: session is not authenticated
			return false;
		}

}		
?>