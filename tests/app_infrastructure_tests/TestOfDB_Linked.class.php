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
        $this->_dbClear();
    }


    function _dbClear() {
        $setUpSql = 'DELETE FROM dblinktest';
        $setUpStmt = $this->DB->prepare($setUpSql);
        $setUpStmt->execute();
    }

    function _dbInsertTestRecord() {
        $insertSql = "INSERT INTO dblinktest VALUES (5,'char data',1,0)";
        $insertStmt = $this->DB->prepare($insertSql);
        $insertStmt->execute();
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

    function testLoadNothing() {
        $this->_dbClear();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB]);

        $this->assertNull($testObj->dblinktest_id);
        $this->assertFalse($testObj->matchesDb);

        Trial_Db_Linked::loadFromDbInto( ['dblinktest_id'=>'1'],$testObj);

        $this->assertFalse($testObj->matchesDb);
        $this->assertNull($testObj->dblinktest_id);
    }

    function testRefreshNothing() {
        $this->_dbClear();
        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB
                                         ,'dblinktest_id'=>'1'
                                        ] );
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,1);

        $testObj->refreshFromDb();
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,1);
    }

    function testLoadSomething() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB]);

        $this->assertNull($testObj->dblinktest_id);
        $this->assertFalse($testObj->matchesDb);

        Trial_Db_Linked::loadFromDbInto( ['dblinktest_id'=>'5'],$testObj);

        $this->assertTrue($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,5);
        $this->assertEqual($testObj->charfield,'char data');
        $this->assertEqual($testObj->intfield,1);
        $this->assertEqual($testObj->flagfield,0);
    }

    function testRefreshSomething() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,'dblinktest_id'=>'5']);
        $this->assertEqual($testObj->dblinktest_id,5);
        $this->assertNull($testObj->charfield);
        $this->assertFalse($testObj->matchesDb);
        
        $testObj->refreshFromDb();
        $this->assertTrue($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,5);
        $this->assertEqual($testObj->charfield,'char data');
        $this->assertEqual($testObj->intfield,1);
        $this->assertEqual($testObj->flagfield,0);
    }

    function testUpdateExistingSomething() {
        $this->_dbClear();
        $this->_dbInsertTestRecord();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB]);
        $this->assertNull($testObj->dblinktest_id);
        $this->assertFalse($testObj->matchesDb);

        Trial_Db_Linked::loadFromDbInto( ['dblinktest_id'=>'5'],$testObj);
        $this->assertTrue($testObj->matchesDb);

        $testObj->charfield = 'new char data';
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->charfield,'new char data');

        $testObj->updateDb();
        $this->assertTrue($testObj->matchesDb);
        $this->assertEqual($testObj->charfield,'new char data');

        $selectSql = "SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE dblinktest_id=5";
        $selectStmt = $this->DB->prepare($selectSql);
        $selectStmt->execute();
        $selectResult = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($selectResult['charfield'],'new char data');
    }

    function testUpdateNewSomethingWithId() {
        $this->_dbClear();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,
                                         'dblinktest_id'=>'7',
                                         'charfield'=>'the stringiest',
                                         'intfield'=>38,
                                         'flagfield'=>true]);
        $this->assertFalse($testObj->matchesDb);
        $this->assertEqual($testObj->dblinktest_id,7);
        $this->assertEqual($testObj->charfield,'the stringiest');
        $this->assertEqual($testObj->intfield,38);
        $this->assertEqual($testObj->flagfield,true);


        $testObj->updateDb();
        $this->assertTrue($testObj->matchesDb);

        $selectSql = "SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE dblinktest_id=7";
        $selectStmt = $this->DB->prepare($selectSql);
        $selectStmt->execute();
        $selectResult = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($selectResult['dblinktest_id'],7);
        $this->assertEqual($selectResult['charfield'],'the stringiest');
        $this->assertEqual($selectResult['intfield'],38);
        $this->assertEqual($selectResult['flagfield'],true);
    }

    function testUpdateNewSomethingWithoutId() {
        $this->_dbClear();

        $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,
                                         'charfield'=>'even stringier',
                                         'intfield'=>42,
                                         'flagfield'=>false]);
        $this->assertFalse($testObj->matchesDb);
        $this->assertNull($testObj->dblinktest_id);
        $this->assertEqual($testObj->charfield,'even stringier');
        $this->assertEqual($testObj->intfield,42);
        $this->assertEqual($testObj->flagfield,false);

        $testObj->updateDb();
        $this->assertTrue($testObj->matchesDb);
        $this->assertNotNull($testObj->dblinktest_id);

        $selectSql = "SELECT dblinktest_id,charfield,intfield,flagfield FROM dblinktest WHERE intfield=42";
        $selectStmt = $this->DB->prepare($selectSql);
        $selectStmt->execute();
        $selectResult = $selectStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($selectResult['dblinktest_id'],$testObj->dblinktest_id);
        $this->assertEqual($selectResult['charfield'],'even stringier');
        $this->assertEqual($selectResult['intfield'],42);
        $this->assertEqual($selectResult['flagfield'],false);
    }

}


?>