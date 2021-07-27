<?php
/**
* MySQL DB engine with using the PHP mysqli library
*
* LICENSE: MIT
*
* @category   Databases
* @package    Gelembjuk/DB
* @copyright  Copyright (c) 2015 Roman Gelembjuk. (http://gelembjuk.com)
* @version    1.0
* @link       https://github.com/Gelembjuk/db
*/

namespace Gelembjuk\DB;

class MySQLi extends MySQL {
	
	protected function getConnection()
	{
		if ($this->connection !== NULL) {
			if ($this->connectiontimelimit > 0 && 
				$this->connectioncreatetime > 0 && 
				time() - $this->connectioncreatetime > $this->connectiontimelimit) {
				$this->closeConnection();
			} else {
				return $this->connection;
			}
		}

		$connstart = microtime(true);
		
		$att=0;

		do {
			// hide errors. 
			$this->connection = @mysqli_connect($this->dbhost, $this->dbuser, $this->dbpassword,$this->dbname);
			$att++;

			if (!$this->connection && $att<4) {
				sleep(1);	// try again in 1 sec
			}

		} while (!$this->connection && $att<4);
		
		if ($this->connection === NULL) {
			throw new Exceptions\DBException('Can not connect to the DB server: '.mysqli_connect_error(),'','connection',1);
		}
		
		if ($this->connectioncharset != '') {
			mysqli_set_charset($this->connection,$this->connectioncharset);
		}

		$conntime = microtime(true) - $connstart;
		
		$this->profilerAction('dbconn',$conntime,"Database connection time $conntime");
		
		$this->connectioncreatetime = time();
		
		foreach ($this->extrainitqueries as $query) {
            mysqli_query($this->connection, $query);
            
            $conntime = microtime(true) - $connstart;
            $this->profilerAction('dbquery',$conntime,"Init SQL query $conntime: $query");
		}
		
		return $this->connection;
	}

	public function closeConnection() {
		if ($this->connection != NULL) {
			mysqli_close($this->connection);
			$this->connection=NULL;		
			$this->connectioncreatetime = 0;
			return TRUE;
		}
		return FALSE;
	}

	public function getRows($query)
	{
		$connstart = microtime(true);

		$res = mysqli_query($this->getConnection(), $query);
		
		$conntime = microtime(true) - $connstart;
		
		$this->profilerAction('dbquery',$conntime,"SQL query execution time $conntime: $query");
		
		$connstart = microtime(true);

		if ($res != NULL) {
			$array = array();

			while ($row = mysqli_fetch_assoc($res)) {
				$array[] = $row;
			}
			mysqli_free_result($res);
			
			$conntime = microtime(true) - $connstart;
		
			$this->profilerAction('dbquery',$conntime,"SQL query result reading time $conntime: $query");
			
			return $array;
		} else	{
			throw new Exceptions\DBException(mysqli_error($this->getConnection()),$query,'query',3);
		}
	}

	public function getRow($query) {
		$connstart = microtime(true);
		
		$result = mysqli_query($this->getConnection(), $query);
		
		$conntime = microtime(true) - $connstart;
		
		$this->profilerAction('dbquery',$conntime,"SQL query execution time $conntime: $query");
		
		if ($result) {
			$row = mysqli_fetch_assoc($result);
			mysqli_free_result($result);

			return($row);
		} else {
			throw new Exceptions\DBException(mysqli_error($this->getConnection()),$query,'query',4);
		}
	}

	public function getValue($query) {
		$connstart = microtime(true);
		
		$result = mysqli_query($this->getConnection(), $query);
		
		$conntime = microtime(true) - $connstart;
		
		$this->profilerAction('dbquery',$conntime,"SQL query execution time $conntime: $query");

		if ($result) {
			$row = mysqli_fetch_row($result);
			return($row[0]);
		} else {
			throw new Exceptions\DBException(mysqli_error($this->getConnection()),$query,'query',5);
		}
	}

	public function executeQuery($query) {
		$connstart = microtime(true);
		
		$result = mysqli_query($this->getConnection(), $query);
		
		$conntime = microtime(true) - $connstart;
		
		$this->profilerAction('dbquery',$conntime,"SQL query execution time $conntime: $query");

		if (!$result && mysqli_errno($this->getConnection()) > 0) {
			throw new Exceptions\DBException(mysqli_error($this->getConnection()),$query,'execute',6);
		}
		return  TRUE;
	}

	public function getLastInsertedId() {
		return  mysqli_insert_id($this->getConnection());
	}

	public function quote($s) {
		return  mysqli_real_escape_string($this->getConnection(),$s);
	}
}
