<?php
/*

Db_Linked is a basic root class for objects that tied to a DB record - e.g. users, eq_groups, eq_items, etc. It provides simple functions to select, insert, and update the DB record associated with the object. NOTE: it does NOT support a delete method, and it does NOT load associations (e.g. group membership). If that kind of functionality is needed it should be implemented in a sub-class (for now, anyway - trying to keep this pretty streamlined).

To use this, make a sub-class of it and set the fields, primaryKeyField, and dbTable atttributes as appropriate. E.g. 

    class Trial_Db_Linked extends Db_Linked {
        public $fields = array('dblinktest_id','charfield','intfield','flagfield');
        public $primaryKeyField = 'dblinktest_id';
        public $dbTable = 'dblinktest';
    }

The create objects of the subclass. When creating objects you must provide a DB connection, and may provide additional initial values for the fields. E.g.

    $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,'dblinktest_id'=>'1']);

Objects also have a field called matchesDb, which indicated whether the values stored in the object match the values stored in the corresponding database record. When a new object is created matchesDb is always false;


You may access the fields listed in the class definition as if they were real attributes. E.g.

    $testObj->charfield = 'some character data';


The class has a static function to load a record from the database.

    $o1 = new Trial_Db_Linked( ['DB'=>$this->DB] );
    Trial_Db_Linked::loadFromDbInto( ['dblinktest_id'=>'1'],$o1);

You can also use the refreshFromDb method of the object itself, which loads information based on the attributes currently set in the object. E.g.

    $o2 = new Trial_Db_Linked( ['DB'=>$this->DB,'dblinktest_id'=>'1'] );
    $o2->refreshFromDb();

NOTE: for either approach, if the data has no matching record then the matchesDb attribute of the object will be false - check that attribute in your code before relying on the object!


The object has a method updateDb which persists the object data in the DB. If there's already a record for the object, then that record is updated. If there is NOT a record, then a new one is inserted.

    $newObj = new Trial_Db_Linked( ['DB'=>$this->DB,'charfield'=>'hello','intfield'=>42,'flagfield'=>false] );
    $newObj->updateDb();

NOTE: if no primary key data is specified in the new object, once the DB is updated the object has the primary key set to the value created by the DB.


See the tests for this class (TestOfDB_Linked.class.php) for more examples.

*/


abstract class Db_Linked
{
    /////////////////////////////////////////////////////
    // this array defined the db-tied properties of this object
    // due to use of magic function __get and __set they may be accessed as if
    // real properties after object creations. E.g.
    //  var $efoo = new Eq_Group();
    //  echo $efoo->name;

    // NOTE: these three attributes need to be defined/set in the sub-class!
    public $fields = array();
    public $primaryKeyField = '';
    public $dbTable = '';

    // NOTE: this is a VERY IMPORTANT attribute - use it to make sure the record matches the database
    public $matchesDb = false;


    public $fieldValues = array();

    public $dbConnection;

    /////////////////////////////////////////////////////

    // NOTE: the initsHash passed to the constructor MUST have at least one entry of DB => a pdo db connection object
    public function __construct($initsHash) {
        if (! isset($initsHash)) {
            $initsHash = array();
        }

        foreach ($this->fields as $fieldName) {
            $initVal = NULL;
            if (array_key_exists($fieldName,$initsHash)) { 
                $initVal = $initsHash[$fieldName];            
            }
            $this->fieldValues[$fieldName] = $initVal;
        }
        $this->matchesDb = false;
        $this->dbConnection = $initsHash['DB'];
    }

    public function __get($name)
    {
         if (array_key_exists($name, $this->fieldValues)) {
             return $this->fieldValues[$name];
         }
         return null;
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->fieldValues)) {
            $this->fieldValues[$name] = $value;
        }
        $this->matchesDb = false;
    }

    /////////////////////////////////////////////////////

    // returns an assoc array derived from $this->fieldValues which will be suitable for use in a PDO execute
    private function _getQueryValuesArray() {
        $qpar = array();
        foreach ($this->fieldValues as $k=>$v) {
            $qpar[':'.$k] = $v;
        }
        return $qpar;
    }

    /////////////////////////////////////////////////////
    
    // returns an empty object of the type of this class
    public static function loadAllFromDb($searchHash) {
        // NOTE: THIS MUST BE OVERRIDDEN/IMPLEMENTED IN THE SUB-CLASS!!
        trigger_error('DB_Linked loadAllFromDb method must be overridden by implementing sub-classes',E_USER_ERROR);   
    }

    protected static function _loadAllFromDb($searchHash,$template) {
        //$template =  self::factory($usingDb);
        $fetchStmt = self::_buildFetchStatement($searchHash,$template);
        $fetchStmt->execute($searchHash);
        return $fetchStmt->fetchAll(PDO::FETCH_CLASS| PDO::FETCH_PROPS_LATE, get_class($template),[['DB'=>$template->dbConnection]]);
    }
    
    // takes: an identity hash - i.e. a hash of col names to values, a recipient object into which the results are loaded
    // NOTE: in the case of multiple rows found, only the first is used
    // NOTE: in the case of no rows found the recipient->matchesDB is false
    public static function loadFromDbInto($identHash,&$recipient) {
        $fetchStmt = self::_buildFetchStatement($identHash,$recipient);
        $fetchStmt->execute($identHash);
        if ($fetchStmt->rowCount() < 1) {
            $recipient->matchesDb = false;
            return;
        }
        $fetchStmt->setFetchMode(PDO::FETCH_INTO, $recipient);
        $fetchStmt->fetch();
        $recipient->matchesDb = true;
    } 

    private static function _buildFetchStatement($identHash,$recipient) {
        $fetchSql = 'SELECT '.implode(',',$recipient->fields).' FROM '.$recipient->dbTable.' WHERE 1=1';
        foreach ($identHash as $k=>$v) {
            $fetchSql .= ' AND '.$k.' = :'.$k;
        }
        $fetchStmt = $recipient->dbConnection->prepare($fetchSql);
        return $fetchStmt;
    }

    /////////////////////////////////////////////////////
    
    public function refreshFromDb() {
        if ($this->matchesDb) {
            return;
        }
        $fetchAttr = array();
        foreach ($this->fields as $fieldName) {
            if (! is_null($this->fieldValues[$fieldName])) { 
                $fetchAttr[$fieldName] = $this->fieldValues[$fieldName];
            }
        }
        
        $fetchStmt = self::_buildFetchStatement($fetchAttr, $this);
        $fetchStmt->execute($fetchAttr);
        if ($fetchStmt->rowCOunt() < 1) {
            $this->matchesDb = false;
            return;
        }
        $fetchStmt->setFetchMode(PDO::FETCH_INTO, $this);
        $fetchStmt->fetch();
        $this->matchesDb = true;
    }

    public function updateDb() {
        if ($this->matchesDb) {
            return;
        }
        $doInsert = (! $this->fieldValues[$this->primaryKeyField]);
        if (! $doInsert) {
            $checkSql = 'SELECT '.$this->primaryKeyField.' FROM '.$this->dbTable.' WHERE '.$this->primaryKeyField.'= :'.$this->primaryKeyField;
            $checkStmt = $this->dbConnection->prepare($checkSql);
            $checkStmt->execute([':'.$this->primaryKeyField => $this->fieldValues[$this->primaryKeyField]]);
            $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $doInsert = ($this->fieldValues[$this->primaryKeyField] != $checkResult[$this->primaryKeyField]);
        }


        if ($doInsert) {
            $insertSql = 'INSERT INTO '.$this->dbTable.' VALUES(:'.$this->primaryKeyField;
            foreach ($this->fields as $k) {
                if ($k != $this->primaryKeyField) {
                    $insertSql .= ', :'.$k;
                }
            }
            $insertSql .= ')';
            $insertStmt = $this->dbConnection->prepare($insertSql);
            $this->fieldValues[$this->primaryKeyField] = $insertStmt->execute($this->_getQueryValuesArray());
            $this->matchesDb = true;
        } 
        else {
            $updateSql = 'UPDATE '.$this->dbTable.' SET '.$this->primaryKeyField.'='.$this->fieldValues[$this->primaryKeyField];
            foreach ($this->fields as $k) {
                if ($k != $this->primaryKeyField) {
                    $updateSql .= ', '.$k.' = :'.$k;
                }
            }
            $updateSql .= ' WHERE '.$this->primaryKeyField.'= :'.$this->primaryKeyField;
            $updateStmt = $this->dbConnection->prepare($updateSql);
            $updateStmt->execute($this->_getQueryValuesArray());

            $this->matchesDb = true;
        }
    }

} 

?>