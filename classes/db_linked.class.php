<?php
/*

Db_Linked is a basic root class for objects that are tied to a DB record - e.g. users, eq_groups, eq_items, etc. It provides simple functions to select, insert, and update the DB record associated with the object. NOTE: it does NOT support a delete method, and it does NOT load associations (e.g. group membership). If that kind of functionality is needed it should be implemented in a sub-class (for now, anyway, we're trying to keep this pretty streamlined).

To use this, make a sub-class of it and set the fields, primaryKeyField, and dbTable atttributes as appropriate. E.g. 

    class Trial_Db_Linked extends Db_Linked {
        public static $fields = array('dblinktest_id','charfield','intfield','flagfield');
        public static $primaryKeyField = 'dblinktest_id';
        public static $dbTable = 'dblinktest';
    }

To create objects of the subclass: when creating objects you must provide a DB connection, and may provide additional initial values for the fields. E.g.

    $testObj = new Trial_Db_Linked( ['DB'=>$this->DB,'dblinktest_id'=>'1']);

Objects also have a field called matchesDb, which indicates whether the values stored in the object match the values stored in the corresponding database record. When a new object is created matchesDb is always false;


You may access the fields listed in the class definition as if they were real attributes. E.g.

    $testObj->charfield = 'some character data';


The class has a static function to load a single object from the database (e.g. load where PK 'dblinktest_id' = 1). This first parameter is a search hash - i.e. a hash where the keys are the names of the fields and the values are used to create conditions of the select statement that retrieves the record from the DB. 
NOTE 1: in the event that multiple records match the search hash, the first (as arbitrarily given by the DB) is used
NOTE 2: if the value in the search hash is scalar then equality is used; if the value is an array then the IN operation is used

    $o1 = Trial_Db_Linked::getOneFromDb( ['dblinktest_id'=>'1'],$DB);

There is also a corresponding static function to load a set of matching objects (e.g. load all where 'intfield' value = 5).

    $objList = Trial_Db_Linked::getAllFromDb( ['intfield'=>'5'],$DB);

and an example using an array as a the value in the search hash

    $objList = Trial_Db_Linked::getAllFromDb( ['intfield'=>[2,3,5]],$DB);

For a single object you can also use the refreshFromDb method of the object itself, which loads information based on the attributes currently set in the object. E.g.

    $o2 = new Trial_Db_Linked( ['DB'=>$DB,'dblinktest_id'=>'1'] );
    $o2->refreshFromDb();

NOTE: for either single-load approach, if the data has no matching record then the matchesDb attribute of the object will be false - check that attribute in your code before relying on the object! Also, if there is more than one matching row then only the first one found will be used (and remember that the ordering coming from the DB is arbitrary)!
	<<EXAMPLE HERE OF CHECKING matchesDB attribute>>


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
    public static $fields = array();
    public static $primaryKeyField = '';
    public static $dbTable = '';

    // NOTE: this is a VERY IMPORTANT attribute - use it to make sure the record matches the database
    public $matchesDb = false;

    public $fieldValues = array();

    public $dbConnection;

	/////////////////////////////////////////////////////

	// "final", but PHP doesn't allow final attributes :(
	public static $ERR_MSG_NO_PK = "missing primary key in db_linked sub-class definition";
	public static $ERR_MSG_NO_TABLE = "missing table name in db_linked sub-class definition";
	public static $ERR_MSG_NO_DB = "no db connection provided to db_linked sub-class constructor";
    public static $ERR_MSG_BAD_DB = "empty db connection provided to db_linked sub-class constructor";
    public static $ERR_MSG_BAD_SEARCH_PARAM = "an invalid value was given in the search hash";

	/////////////////////////////////////////////////////

	// NOTE: the initsHash passed to the constructor MUST have at least one entry of DB => a pdo db connection object
    public function __construct($initsHash) {

		if (! static::$primaryKeyField) {
			trigger_error(Db_Linked::$ERR_MSG_NO_PK,E_USER_ERROR);
			return;
		}
		if (! static::$dbTable) {
			trigger_error(Db_Linked::$ERR_MSG_NO_TABLE,E_USER_ERROR);
			return;
		}

		if (! isset($initsHash)) {
			$initsHash = array();
		}

		if (! array_key_exists('DB',$initsHash)) {
			trigger_error(Db_Linked::$ERR_MSG_NO_DB,E_USER_ERROR);
			return;
		}

		if ($initsHash['DB'] == '') {
			# consider a more rigorous comparison, instead of simply empty string?
			trigger_error(Db_Linked::$ERR_MSG_BAD_DB,E_USER_ERROR);
			return;
		}

        foreach (static::$fields as $fieldName) {
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
            if ($this->fieldValues[$name] !== $value) {
	            $this->fieldValues[$name] = $value;
    		    $this->matchesDb = false;
    		}
        }
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

    public static function arrayToPkHash($arrayOfDbLinkedObjects) {
        $pkHash = [];
        $pkField = static::$primaryKeyField;
        foreach ($arrayOfDbLinkedObjects as $obj) {
            $pkHash[$obj->$pkField] = $obj;
        }
        return $pkHash;
    }


    public static function getAllFromDb($searchHash,$usingDb) {
        $whichClass = get_called_class();
        $fetchStmt = self::_buildFetchStatement($searchHash,$usingDb);
        $fetchStmt->execute($searchHash);
        $res = $fetchStmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $whichClass,[['DB'=>$usingDb]]);
        for ($i=count($res)-1;$i>=0;$i--) {
            $res[$i]->matchesDb = true;
        }
        return $res;
    }
    
    // takes: an identity hash - i.e. a hash of col names to values, a database connection
    // returns: an object of the appriate type with values loaded from the DB
    // NOTE: in the case of multiple rows found, only the first is used
    // NOTE: in the case of no rows found the recipient->matchesDB is false
    public static function getOneFromDb($searchHash,$usingDb) {
        $fetchStmt = self::_buildFetchStatement($searchHash,$usingDb);
        $fetchStmt->execute($searchHash);

        $whichClass = get_called_class();
        $recipient = new $whichClass(['DB'=>$usingDb]);

        if ($fetchStmt->rowCount() < 1) {
            $recipient->matchesDb = false;
            return $recipient;
        }
        $fetchStmt->setFetchMode(PDO::FETCH_INTO|PDO::FETCH_PROPS_LATE, $recipient);
        $fetchStmt->fetch();
        $recipient->matchesDb = true;
        return $recipient;
    } 

    // takes: a hash of field names to values (the latter may be scalar or array)
    // returns: a prepared select statement based on the data in the hash
    // SIDE EFFECT: if any of the values in the hash are arrays then the hash will be altered to create those values top-level keys and to remove the initial top-level key - this enables the hash to be used in the execute statement later
    private static function _buildFetchStatement(&$identHash,$usingDb) {
        $fetchSql = static::buildFetchSql($identHash);

        $fetchStmt = $usingDb->prepare($fetchSql);

        return $fetchStmt;
    }

    // takes: a hash of field names to values (the latter may be scalar or array)
    // returns: a select statement based on the data in the hash
    // SIDE EFFECT: if any of the values in the hash are arrays then the hash will be altered to create those values top-level keys and to remove the initial top-level key - this enables the hash to be used in the execute statement later
    public static function buildFetchSql(&$identHash) {
        $fetchSql = 'SELECT '.implode(',',static::$fields).' FROM '.static::$dbTable.' WHERE 1=1';
        $keys_to_remove = [];
        $key_vals_to_add = [];
        foreach ($identHash as $k=>$v) {
            if (is_array($v)) {
                if (count($v) <= 0) {
                    trigger_error(Db_Linked::$ERR_MSG_BAD_SEARCH_PARAM,E_USER_ERROR);
                    return;
                }
                array_push($keys_to_remove,$k);
                $fetchSql .= ' AND '.$k.' IN (';
                for ($i = 0,$numElts = count($v); $i<$numElts;$i++) {
                    $newKey = "__$k$i";
                    $key_vals_to_add[$newKey] = $v[$i];
                    if ($i > 0) { $fetchSql .= ','; }
                    $fetchSql .= ":$newKey";
                }
                $fetchSql .= ')';
            }
            else {
                $fetchSql .= ' AND '.$k.' = :'.$k;
            }
        }
        foreach ($keys_to_remove as $k) {
            unset($identHash[$k]);
        }
        foreach ($key_vals_to_add as $k=>$v) {
            $identHash[$k] = $v;
        }

        return $fetchSql;
    }

    /////////////////////////////////////////////////////
    
    public function refreshFromDb() {
        if ($this->matchesDb) {
            return;
        }
        $fetchAttr = array();
        foreach (static::$fields as $fieldName) {
            if (! is_null($this->fieldValues[$fieldName])) { 
                $fetchAttr[$fieldName] = $this->fieldValues[$fieldName];
            }
        }
        
        $fetchStmt = self::_buildFetchStatement($fetchAttr, $this->dbConnection);
        $fetchStmt->execute($fetchAttr);
        if ($fetchStmt->rowCOunt() < 1) {
            $this->matchesDb = false;
            return;
        }
        $fetchStmt->setFetchMode(PDO::FETCH_INTO, $this);
        $fetchStmt->fetch();
        $this->matchesDb = true;
    }

    public function updateDb($debug=0) {
        if ($debug) { echo "<pre>\n"; }
        if ($this->matchesDb) {
            return;
        }
        $doInsert = (! $this->fieldValues[static::$primaryKeyField]);
        if (! $doInsert) {
            $checkSql = 'SELECT '.static::$primaryKeyField.' FROM '.static::$dbTable.' WHERE '.static::$primaryKeyField.'= :'.static::$primaryKeyField;
            $checkStmt = $this->dbConnection->prepare($checkSql);
            $checkStmt->execute([':'.static::$primaryKeyField => $this->fieldValues[static::$primaryKeyField]]);
            $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $doInsert = ($this->fieldValues[static::$primaryKeyField] != $checkResult[static::$primaryKeyField]);
        }

        if ($debug) { echo "doInsert is $doInsert\n"; }
        if ($doInsert) {
            $insertSql = 'INSERT INTO '.static::$dbTable.' VALUES(:'.static::$primaryKeyField;
            foreach (static::$fields as $k) {
                if ($k != static::$primaryKeyField) {
                    $insertSql .= ', :'.$k;
                }
            }
            $insertSql .= ')';
            if ($debug) { echo "insertSql is $insertSql\n"; }

            $insertStmt = $this->dbConnection->prepare($insertSql);
            $res = $insertStmt->execute($this->_getQueryValuesArray());
            if ($debug) { print_r($insertStmt->errorInfo()); }
            if ($debug) { print_r($this->_getQueryValuesArray()); }
            $this->fieldValues[static::$primaryKeyField] = $res;
            $this->matchesDb = true;
        } 
        else {
            $updateSql = 'UPDATE '.static::$dbTable.' SET '.static::$primaryKeyField.'='.$this->fieldValues[static::$primaryKeyField];
            foreach (static::$fields as $k) {
                if ($k != static::$primaryKeyField) {
                    $updateSql .= ', '.$k.' = :'.$k;
                }
            }
            $updateSql .= ' WHERE '.static::$primaryKeyField.'= :'.static::$primaryKeyField;

            if ($debug) { echo "updateSql is $updateSql\n"; }

            $updateStmt = $this->dbConnection->prepare($updateSql);
            $updateStmt->execute($this->_getQueryValuesArray());
            if ($debug) { print_r($updateStmt->errorInfo()); }
            $this->matchesDb = true;
        }

        if ($debug) { echo "</pre>\n"; }
    }

} 

?>