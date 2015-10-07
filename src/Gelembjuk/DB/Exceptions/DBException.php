<?php

namespace Gelembjuk\DB\Exceptions;

/*
* This class helps to display uncatched error to a user
*/

class DBException extends \Exception{
	protected $sql;
	protected $textcode;
	
	public function __construct($message = '', $sql = '' , $textcode = '', $number = 0) {
		parent::__construct($message,$number);
		$this->textcode = $textcode;
		$this->sql = $sql;
	}
	public function getSQL() {
		return $this->sql;
	}
	public function getTextCode() {
		return $this->textcode;
	}
	public function getLogInfo() {
		return 'Error message: '.$this->getMessage().'. Error Code: '.$this->getTextCode().'('.$this->getCode().'). SQL: '.$this->getSQL();
	}
}