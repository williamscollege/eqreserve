<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/db_linked.class.php';

class Trial_Db_Linked extends Db_Linked {
    public $fields = array('dblinktest_id','charfield','intfield','flagfield');
    public $dbTable = 'dblinktest';
    //public function __construct($initsHash) { parent::__construct($initsHash); }

}

class TestOfDB_Linked extends UnitTestCaseDB {


    function testConnectedToDatabase() {
       $this->assertNotNull($this->DB);
    }

    function testGetSet() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertEqual($testObj->dblinktest_id,1);
        $this->assertEqual($testObj->dblinktest_id,$testObj->fieldValues['dblinktest_id']);
    }

    function testSelectNothing() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertFalse($testObj->matchesDb);
        $testObj->refreshFromDb();
        $this->assertFalse($testObj->matchesDb);
    }
    
}
?>