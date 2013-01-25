<?php
abstract trait Db_Linked
{
    /////////////////////////////////////////////////////
    // this array defined the db-tied properties of this object
    // due to use of magic function __get and __set they may be accessed as if
    // real properties after object creations. E.g.
    //  var $efoo = new Eq_Group();
    //  echo $efoo->name;
    public $id;
    public $date_created;
    public $date_updated;
    public $name;
    public $description;

    public abstract static $fields;
    private abstract static $dbTable;

    public $fieldValues = [];


    public $matchesDb = false;

    /////////////////////////////////////////////////////

    function __construct($initsHash=[]) {
        var $initVal;        
        foreach (self::$fields as $fieldName) {
            $initVal = '';
            if (array_key_exists($fieldName,$initsHash)) { 
                $initVal = $initsHash[$fieldName];            
            }
            $this->fieldValues[$fieldName] = $initVal;
        }
        $this->matchesDb = false;
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
    
    // takes: an identity hash - i.e. a hash of col names to values
    // returns: a new object with attributes set to the corresponding values from the DB
    // NOTE: in the case of multiple rows found, only the first is used
    // NOTE: in the case of no rows found, null is returned
    public static function loadFromDb($identHash) {
        var $fetchStmt = self::_buildFetchStatement($identHash);
        $fetchStmt->setFetchMode(PDO::FETCH_INTO, new Eq_Group());
        $fetchStmt->execute($identHash);
        var $newGroup = $fetchStmt->fetch();
        $newGroup->matchesDb = true;
        return $newGroup;
    } 

    private static function _buildFetchStatement($identHash) {
        // construct the SQL statement
        var $fetchSql = 'SELECT '.implode(',',self::$fields).' FROM '.self::$dbTable.' WHERE 1=1';
        foreach ($identHash as $k=>$v) {
            $fetchSql .= ' AND '.$k.' = :'.$k;
        }
        var $fetchStmt = $DB->prepare($fetchSql);
        return $fetchStmt;
    }

    /////////////////////////////////////////////////////
    
    public function refreshFromDb() {
        if ($this->matchesDb) {
            return;
        }
        if (! $this->id) {            
            return;
        }
        var $fetchStmt = self::_buildFetchStatement(['id'=>this->$id]);
        $fetchStmt->setFetchMode(PDO::FETCH_INTO, $this);
        $fetchStmt->execute(['id'=>this->$id]);
        $fetchStmt->fetch();
        $this->matchesDb = true;
    }

    public function updateDb() {
        if ($this->matchesDb) {
            return;
        }
        if (! $this->id) {
            var $insertSql = 'INSERT INTO '.self::$dbTable.' VALUES(NULL';
            foreach ($this->fieldValues as $k=>$v) {
                $insertSql .= ', :'.$k;
            }
            $insertSql .= ')';
            var $insertStmt = $DB->prepare($insertSql);
            $this->id = $insertStmt->execute($this->fieldValues);
            $this->matchesDb = true;
        } 
        else {
            var $updateSql = 'UDPATE '.self::$dbTable.' SET id=id';
            foreach ($this->fieldValues as $k=>$v) {
                $updateSql .= ', '.$k.' = :'.$k;
            }
            $updateSql .= ' WHERE id= :id';
            var $updateStmt = $DB->prepare($updateSql);
            $updateStmt->execute($this->fieldValues);
            $this->matchesDb = true;
        }
    }

} 

?>