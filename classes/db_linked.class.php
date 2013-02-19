<?php
class Db_Linked
{
    /////////////////////////////////////////////////////
    // this array defined the db-tied properties of this object
    // due to use of magic function __get and __set they may be accessed as if
    // real properties after object creations. E.g.
    //  var $efoo = new Eq_Group();
    //  echo $efoo->name;

    public $fields = array();
    public $primaryKeyField = '';
    public $dbTable = '';

    public $fieldValues = array();
    public $matchesDb = false;

    public $dbConnection;

    /////////////////////////////////////////////////////

    public function __construct($initsHash) {
        if (! isset($initsHash)) {
            $initsHash = array();
        }
//print "class is ".get_class($this)."<br/>\n";
//print_r($initsHash);
//print_r($this->fields );

        foreach ($this->fields as $fieldName) {
// print "fieldName is $fieldName<br/>\n";
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
        $recipient = $fetchStmt->fetch(PDO::FETCH_INTO);
        $recipient->matchesDb = true;
    } 

    private static function _buildFetchStatement($identHash,$recipient) {
        // construct the SQL statement
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

            //TODO: figure out a better way to handle tricky values: '',0,false
//            if ($this->fieldValues[$fieldName] !== '') { 
//                $fetchAttr[$fieldName] = $this->fieldValues[$fieldName];
//            }

            if (! is_null($this->fieldValues[$fieldName])) { 
                $fetchAttr[$fieldName] = $this->fieldValues[$fieldName];
            }
        }
        
        $fetchStmt = self::_buildFetchStatement($fetchAttr, $this);
        $fetchStmt->setFetchMode(PDO::FETCH_INTO, $this);
        $fetchStmt->execute($fetchAttr);
        if ($fetchStmt->rowCOunt() < 1) {
            $this->matchesDb = false;
            return;
        }
        $fetchStmt->fetch();
        $this->matchesDb = true;
    }

    public function updateDb() {
        if ($this->matchesDb) {
            return;
        }
        if (! $this->fieldValues[$this->primaryKeyField]) {
            $insertSql = 'INSERT INTO '.$this->dbTable.' VALUES(NULL';
            foreach ($this->fieldValues as $k=>$v) {
                if ($k != $this->primaryKeyField) {
                    $insertSql .= ', :'.$k;
                }
            }
            $insertSql .= ')';
            $insertStmt = $this->dbConnection->prepare($insertSql);
            $this->id = $insertStmt->execute($this->fieldValues);
            $this->matchesDb = true;
        } 
        else {
            $updateSql = 'UDPATE '.$this->dbTable.' SET '.$this->primaryKeyField.'='.$this->fieldValues[$this->primaryKeyField];
            foreach ($this->fieldValues as $k=>$v) {
                if ($k != $this->primaryKeyField) {
                    $updateSql .= ', '.$k.' = :'.$k;
                }
            }
            $updateSql .= ' WHERE '.$this->primaryKeyField.'= :'.$this->primaryKeyField;
            $updateStmt = $this->dbConnection->prepare($updateSql);
            $updateStmt->execute($this->fieldValues);
            $this->matchesDb = true;
        }
    }

} 

?>