<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
require_once dirname(__FILE__) . '/../../classes/db_linked.class.php';

class Trial_Db_Linked extends Db_Linked {
    public $fields = array('dblinktest_id','charfield','intfield','flagfield');
    public $primaryKeyField = 'dblinktest_id';
    public $dbTable = 'dblinktest';
    //public function __construct($initsHash) { parent::__construct($initsHash); }

}

class TestOfDB_Linked extends UnitTestCaseDB {


    function setUp() {
        $this->_clearDb();
    }


    function _clearDb() {
        $setUpSql = 'DELETE FROM dblinktest';
        $setUpStmt = $this->DB->prepare($setUpSql);
        $setUpStmt->execute();
    }

    function testConnectedToDatabase() {
       $this->assertNotNull($this->DB);
    }

    function testInitializing() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertEqual($testObj->dblinktest_id,1);
        $this->assertEqual($testObj->dblinktest_id,$testObj->fieldValues['dblinktest_id']);
    }

    function testGetSet() {
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertTrue(is_null($testObj->charfield));

        $testObj->charfield = 'hello world';

        $this->assertEqual($testObj->charfield,'hello world');
        $this->assertEqual($testObj->charfield,$testObj->fieldValues['charfield']);
    }

    function testSelectNothing() {
        $this->_clearDb();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB]);

        $this->assertNull($testObj->dblinktest_id);
        $this->assertFalse($testObj->matchesDb);

        Trial_Db_Linked::loadFromDbInto( ['dblinktest_id'=>'1'],$testObj);

        $this->assertFalse($testObj->matchesDb);
        $this->assertNull($testObj->dblinktest_id);
    }

    function testRefreshNothing() {
        $this->_clearDb();
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,1);
        $testObj->refreshFromDb();
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,1);
    }
    
}
?>