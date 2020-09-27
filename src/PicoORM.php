<?php
/**
 * Package PicoORM
 *
 * @author  Paige Julianne Sullivan <paige@paigejulianne.com>
 * @license MIT
 */

class PicoORM {
	
	/**
	 * @var number numeric ID of row in database
	 */
	public $_id;
	
	/**
	 * @var array:mixed holds the columns out of the database table
	 */
	public $properties;
	
	/**
	 * @var array list of columns that were "tainted" that need to be updated
	 */
	public $_taintedItems;
	
	/**
	 * @var string name of ID column in database (usually 'id', but not always)
	 */
	public $_id_column;
	
	/**
	 * constructor
	 *
	 * @param  mixed   $id_value
	 * @param  string  $id_column
	 */
	public function __construct($id_value, $id_column = 'id') {
		$result = self::_fetch('SELECT * FROM _DB_ WHERE ' . $id_column . ' = ?', $id_value);
		if ($result) {
			$this->_id        = $id_value;
			$this->_id_column = $id_column;
			$this->properties = $result;
			
			return $this;
		}
	}
	
	/**
	 * destructor
	 */
	public function __destruct() {
		$this->writeChanges();
	}
	
	/**
	 * write properties to the database immediately
	 */
	public function writeChanges() {
		if ($this->_taintedItems) {
			foreach ($this->_taintedItems as $propname => $_) {
				if ($propname[0] == '_') {
					continue;
				}
				$parts[]  = "$propname = ?";
				$values[] = $this->properties[$propname];
			}
			if ($parts || $values) {
				$sql = "UPDATE _DB_ SET " . implode(', ', $parts) . " WHERE " . $this->_id_column . " = " . $this->_id;
				
				$result = self::_doQuery($sql, array_values($values));
			}
		}
	}
	
	/**
	 * gets a property
	 *
	 * @param  string  $prop
	 *
	 * @return mixed
	 */
	public function __get($prop) {
		return $this->properties[$prop];
	}
	
	/**
	 * sets a property
	 *
	 * @param  string  $prop
	 * @param  mixed   $value
	 */
	public function __set($prop, $value) {
		$this->_taintedItems[$prop] = $prop;
		$this->properties[$prop] = $value;
		$this->writeChanges();
	}
	
	/**
	 * sets a number of properties from an array
	 *
	 * @param  array:mixed $array
	 */
	public function setMulti($array) {
		foreach ($array as $prop => $value) {
			$this->__set($prop, $value);
		}
	}
	
	/**
	 * ! DANGER WILL ROBINSON ! deletes the current row from the database and destroys the object
	 */
	public function delete() {
		self::_doQuery('DELETE FROM _DB_ WHERE ' . $this->_id_column . ' = ?', $this->_id);
	}
	
	/**
	 * check to see if a record already exists in the database
	 *
	 * @param  string  $column
	 * @param  string  $value
	 */
	static public function checkForDuplicate($column, $value) {
		$sql = sprintf('SELECT %s FROM _DB_ WHERE %1$s = ?', $column);
		
		return self::_fetch($sql, array($value));
	}
	
	/**
	 * retrives multiple rows/objects from the database based on parameters
	 *
	 * @param  string  $idColumn
	 * @param  array[string|string|mixed] $filters column|op|data
	 * @param  string  $filterGlue  join statement for filters
	 * @param  string  $forceArray  force an array even if only a single result is returned
	 *
	 * @return boolean|array:object|object
	 */
	static public function getAllObjects(
		$idColumn = 'id', $filters = array(), $filterGlue = 'AND', $forceArray = FALSE
	) {
		$_class = get_called_class();
		
		// this is to build the string that will be used as the expression by PDO
		foreach ($filters as $filter) {
			if (isset($filter[2])) {
				$filterArray[] = $filter[0] . ' ' . $filter[2] . ' ?';
				$dataArray[]   = $filter[3];
			}
		}
		
		if ($filters) {
			$filterString = ' WHERE ' . implode(' ' . $filterGlue . ' ', $filterArray);
		}
		
		$sql    = 'SELECT ' . $idColumn . ' FROM _DB_' . $filterString;
		$result = self::_fetchAll($sql, $dataArray);
		if ( ! $result) {
			return FALSE;
		}
		
		if (count($result) == 1) {
			if ($forceArray) {
				return array(new $_class($result[0][$idColumn]));
			} else {
				return new $_class($result[0][$idColumn]);
			}
		} else {
			foreach ($result as $table_row) {
				$returnArray[$table_row[$idColumn]] = new $_class($table_row[$idColumn]);
			}
			
			return $returnArray;
		}
	}
	
	/**
	 * create a new record in the database
	 *
	 * @param  array:mixed $valueArray column properties/values
	 *
	 * @return boolean|object
	 */
	static public function createNew($valueArray) {
		$_class = get_called_class();
		foreach ($valueArray as $column => $value) {
			$setString[] = "$column = :$column";
		}
		$sql    = "INSERT INTO _DB_ SET " . implode(', ', $setString);
		$result = self::_doQuery($sql, $valueArray);
		
		return $result;
	}
	
	/**
	 * fetch all matching records from the database
	 *
	 * @param  string  $sql         PDO ready sql statement
	 * @param  string  $valueArray  properties and values for PDO substitution
	 * @param  string  $database    technically the table name
	 *
	 * @return array:multi|boolean
	 */
	static public function _fetchAll($sql, $valueArray = NULL, $database = NULL) {
		$statement = self::_doQuery($sql, $valueArray, $database);
		if ($statement->rowCount()) {
			return $statement->fetchAll(PDO::FETCH_ASSOC);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * fetch the first matching record from the database
	 *
	 * @param  string  $sql         PDO ready sql statement
	 * @param  string  $valueArray  properties and values for PDO substitution
	 * @param  string  $database    technically the table name
	 *
	 * @return array|boolean
	 */
	static public function _fetch($sql, $valueArray = NULL, $database = NULL) {
		$statement = self::_doQuery($sql, $valueArray, $database);
		if ($statement->rowCount()) {
			return $statement->fetch(PDO::FETCH_ASSOC);
		} else {
			return FALSE;
		}
	}
	
	/**
	 * executes a sql statement and returns a PDO statement
	 *
	 * @param  string  $sql         PDO ready sql statement
	 * @param  array   $valueArray  properties and values for PDO substitution
	 * @param  string  $database    technically the table name
	 *
	 * @return PDOStatement
	 */
	static public function _doQuery($sql, $valueArray = array(), $database = NULL) {
		if (!is_object($GLOBALS['_PICO_PDO'])) {
			throw new Exception('$GLOBALS["_PICO_PDO"] must be defined as a PDO connection');
		}
		if ($database === NULL) {
			$database = strtolower(get_called_class());
		}

		@list($database, $table) = explode('\\', $database);
		if (@$table) {
			$database .= '.' . $table;
		}
		
		$sql      = str_replace('_DB_', $database, $sql);
		$sql      = str_replace('_db_', $database, $sql);
		
		if ( ! is_array($valueArray)) {
			$valueArray = array($valueArray);
		}
		
		$statement = $GLOBALS['_PICO_PDO']->prepare($sql);
		$statement->execute($valueArray);
		
		return $statement;
	}
}
