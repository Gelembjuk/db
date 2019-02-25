<?php
/**
* The base class for DB functionality units (tables)
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

use Exceptions\DBException as DBException;

class Base {
	use \Gelembjuk\Logger\ApplicationLogger;
	use \Gelembjuk\Locale\GetTextTrait;
	
	protected $dbobject;
	protected $application;
	protected $tableprefix;

	public function __construct($dbobject,$application = null) {
		$this->dbobject = $dbobject;
		$this->application = $application;
		$this->tableprefix = $this->dbobject->getTablePrefix();
	}
	public function init($options) {
	}

	protected function getLastInsertedId() {
		try {
			return $this->dbobject->getLastInsertedId();
		} catch (\Exception $exception) {
			$this->processError($exception);
		}
	}

	protected function executeQuery($sql){
		try {
			$this->dbobject->executeQuery($sql);
		} catch (\Exception $exception) {
			$this->processError($exception);
		}
	}

	public function getRowById($id) {
		return null;
	}
	protected function getValue($sql) {
		try {
			return $this->dbobject->getValue($sql);
		} catch (\Exception $exception) {
			$this->processError($exception);
		}
	}
	protected function getRow($sql) {
		if (trim($sql) == '') {
			return null;
		}
		try {
			return $this->dbobject->getRow($sql);
		} catch (\Exception $exception) {
			$this->processError($exception);
		}
	}

	protected function getRows($sql) {
		if (trim($sql) == '') {
			return array();
		}
		try {
			return $this->dbobject->getRows($sql);
		} catch (\Exception $exception) {
			$this->processError($exception);
		}
	}

	public function getEmptyRecord($table) {
		try {
			$list = $this->getRows("SHOW COLUMNS FROM $table");
			$object = array();
	
			foreach($list as $i){
				$val = '';
	
				if (preg_match('!int!',$i['Type'])) {
					$val = '0';
				}
				$object[$i['Field']] = $val;
			}
			return $object;
		} catch (\Exception $exception) {
			$this->processError($exception);
		}
	}
	protected function quote($s) {
		try {
			return $this->dbobject->quote($s);
		} catch (\Exception $exception) {
			$this->processError($exception);
		}
	}
	protected function processError($exception) {
		$logtext = $exception->getMessage();

		if ($exception instanceof Exceptions\DBException) {
			$logtext = $exception->getLogInfo();
		}

		$this->error($logtext,array('group'=>'dbengine','exception'=>$exception));
		throw new \Exception($this->_('dboperationerror'));
	}
	// add prefix to a table name
	protected function table($table) {
		return $this->tableprefix.$table;
	}
	protected function int($val) {
		return strval(intval($val));// convert to int but as a string
	}
	protected function float($val) {
        return strval(floatval($val));// convert to int but as a string
    }
}
