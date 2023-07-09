<?php
/**
* SQLite DB engine with using the PHP sqlite3 library
*
* LICENSE: MIT
*
* @category   Databases
* @package    Gelembjuk/DB
* @copyright  Copyright (c) 2021 Roman Gelembjuk. (http://gelembjuk.com)
* @version    1.0
* @link       https://github.com/Gelembjuk/db
*/

namespace Gelembjuk\DB;

class SQLite implements EngineInterface {
	use \Gelembjuk\Logger\ApplicationLogger;
	
	protected $application;
	
	protected $connection;
	
	protected $dbfile = '';
	
	protected $tableprefix;
	
	public function __construct($options = array()) {
		$this->application = null;
		
		if (isset($options['application'])) {
			$this->application = $options['application'];
		}
		
		$this->connection = NULL;
		$this->dbfile = $options['databasefile'];
		
		$this->tableprefix = ($options['tableprefix'] != '')?$options['tableprefix']:'';
	}
	
	protected function profilerAction($type,$time,$string) {
		if ($this->application) {
			return $this->application->profilerAction($type,$time,$string);
		}
		return null;
	}
	
	protected function getConnection()
	{
		if ($this->connection !== NULL) {
			return $this->connection;
		}
		
		if (!file_exists($this->dbfile)) {
            throw new Exceptions\DBException('DB file is not found','','connection',1);
		}

		// hide errors. 
        $this->connection = new \SQLite3($this->dbfile);
		
		if (!$this->connection) {
			throw new Exceptions\DBException('Can not connect to the DB file','','connection',1);
		}
		
		return $this->connection;
	}

	public function closeConnection() {
		if ($this->connection != NULL) {
            unset($this->connection);
			$this->connection=NULL;	
			return TRUE;
		}
		return FALSE;
	}

	public function getRows($query)
	{
		$res = $this->getConnection()->query($query);

		if ($res == NULL) {
            throw new Exceptions\DBException("Failed to get query results",$query,'query',3);
		}
		
        $array = array();

        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $array[] = $row;
        }
        unset($res);
        
        return $array;
	}

	public function getRow($query) {
		$result = $this->getConnection()->query($query);
		
		if (!$result) {
            throw new Exceptions\DBException("Query failed",$query,'query',4);
		}
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        unset($result);

        return($row);
	}

	public function getValue($query) {
		$result = $this->getConnection()->query($query);

		if (!$result) {
            throw new Exceptions\DBException("Query failed",$query,'query',4);
		}
		
        $row = $result->fetchArray(SQLITE3_NUM);
        
        unset($result);

        return($row[0]);
	}

	public function executeQuery($query) {
		$this->getConnection()->query($query);

		return  TRUE;
	}
	
	public function executeQueryWithBlob($queryStmt, $blobs) 
	{
		$c = $this->getConnection();
		
		$query = $c->prepare($queryStmt);
		
		if ($query === false) {
            throw new Exceptions\DBException($c->lastErrorMsg(),4);
		}
		
		$p = 1;
		foreach ($blobs as $blob) {
            $query->bindParam($p, $blob, SQLITE3_BLOB);
            $p++;
        }
        $query->execute();

		return  TRUE;
	}

	public function getLastInsertedId() {
		return  $this->getConnection()->lastInsertRowID();
	}

	public function quote($s) {
		return  $this->getConnection()->escapeString($s);
	}

	public function getTablePrefix() {
		return $this->tableprefix;
	}
}
