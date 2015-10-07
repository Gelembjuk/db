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
			throw new Exceptions\DBException('Can not connect to the DB server: '.mysql_error(),'','connection',1);
		}
		
		if ($this->connectioncharset != '') {
			mysqli_set_charset($this->connection,$this->connectioncharset);
		}

		$this->connectioncreatetime = time();

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
		$starttime=time();

		$res = mysqli_query($query, $this->getConnection());

		if ($res != NULL) {
			$array = array();

			while ($row = mysqli_fetch_assoc($res)) {
				$array[] = $row;
			}
			mysqli_free_result($res);
			return $array;
		} else	{
			throw new Exceptions\DBException(mysql_error(),$query,'query',3);
		}
	}

	public function getRow($query) {
		$result = mysqli_query($query, $this->getConnection());
		
		if ($result) {
			$row = mysqli_fetch_assoc($result);
			mysqli_free_result($result);

			return($row);
		} else {
			throw new Exceptions\DBException(mysql_error(),$query,'query',4);
		}
	}

	public function getValue($query) {
		$result = mysqli_query($query, $this->getConnection());

		if ($result) {
			$row = mysqli_fetch_row($result);
			return($row[0]);
		} else {
			throw new Exceptions\DBException(mysql_error(),$query,'query',5);
		}
	}

	public function executeQuery($query) {
		mysqli_query($query, $this->getConnection());

		if(mysqli_errno()>0) {
			throw new Exceptions\DBException(mysql_error(),$query,'execute',6);
		}
		return  TRUE;
	}

	public function getLastInsertedId() {
		return  mysqli_insert_id($this->getConnection());
	}

	public function quote($s) {
		return  mysqli_real_escape_string($s,$this->getConnection());
	}
}
