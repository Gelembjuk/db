<?php
/**
* MySQL DB engine with using the PHP mysql library
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

class MySQL implements EngineInterface {
	use \Gelembjuk\Logger\ApplicationLogger;
	
	protected $application;
	
	protected $connection;
	protected $connectioncreatetime = 0;
	protected $connectiontimelimit = 0;
	protected $connectioncharset = '';
	protected $namescharset = '';
	
	protected $dbhost = '';
	protected $dbname = '';
	protected $dbuser = '';
	protected $dbpassword = '';
	
	protected $tableprefix;
	
	public function __construct($options = array()) {
		$this->application = null;
		
		if (isset($options['application'])) {
			$this->application = $options['application'];
		}
		
		$this->connection = NULL;
		$this->dbhost = $options['host'];
		$this->dbname = $options['database'];
		$this->dbuser = $options['user'];
		$this->dbpassword = $options['password'];
		
		$this->connectioncharset = $options['connectioncharset'];
		$this->namescharset = $options['namescharset'];

		$this->connectiontimelimit = ($options['connectiontimelimit'] > 0)?$options['connectiontimelimit']:0;
		
		$this->tableprefix = ($options['tableprefix'] != '')?$options['tableprefix']:'';

	}
	
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
			$this->connection = @mysql_connect($this->dbhost, $this->dbuser, $this->dbpassword,true);
			$att++;

			if (!$this->connection && $att<4) {
				sleep(1);	// try again in 1 sec
			}

		} while (!$this->connection && $att<4);
		
		if (!$this->connection) {
			throw new Exceptions\DBException('Can not connect to the DB server: '.mysql_error(),'','connection',1);
		}
		
		if ($this->connectioncharset != '') {
			mysql_set_charset($this->connectioncharset,$this->connection);
		}

		if (!@mysql_select_db($this->dbname,$this->connection)) {
			throw new Exceptions\DBException("Can not connect to the DB ".$this->dbname.': '.mysql_error(),'','connection',2);
		}
		
		if ($this->namescharset != '') {
			mysql_query("SET NAMES '".$this->namescharset."'", $this->connection);
		}

		$this->connectioncreatetime = time();

		return $this->connection;
	}

	public function closeConnection() {
		if ($this->connection != NULL) {
			mysql_close($this->connection);
			$this->connection=NULL;		
			$this->connectioncreatetime = 0;
			return TRUE;
		}
		return FALSE;
	}

	public function getRows($query)
	{
		$starttime=time();

		$res = mysql_query($query, $this->getConnection());

		if ($res != NULL) {
			$array = array();

			while ($row = mysql_fetch_assoc($res)) {
				$array[] = $row;
			}
			mysql_free_result($res);
			return $array;
		} else	{
			throw new Exceptions\DBException(mysql_error(),$query,'query',3);
		}
	}


	public function getRow($query) {
		$result = mysql_query($query, $this->getConnection());
		
		if ($result) {
			$row = mysql_fetch_assoc($result);
			mysql_free_result($result);

			return($row);
		} else {
			throw new Exceptions\DBException(mysql_error(),$query,'query',4);
		}
	}

	public function getValue($query) {
		$result = mysql_query($query, $this->getConnection());

		if ($result) {
			$row = mysql_fetch_row($result);
			return($row[0]);
		} else {
			throw new Exceptions\DBException(mysql_error(),$query,'query',5);
		}
	}

	public function executeQuery($query) {
		mysql_query($query, $this->getConnection());

		if(mysql_errno()>0) {
			throw new Exceptions\DBException(mysql_error(),$query,'execute',6);
		}
		return  TRUE;
	}

	public function getLastInsertedId() {
		return  mysql_insert_id($this->getConnection());
	}

	public function quote($s) {
		return  mysql_real_escape_string($s,$this->getConnection());
	}

	public function getTablePrefix() {
		return $this->tableprefix;
	}
}
