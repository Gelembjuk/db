<?php
/**
* The interface Gelembjuk\DB\EngineInterface is used to build the set of DB access engines and be able to change them in an application with minimum code changes
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

interface EngineInterface {
	public function __construct($options = array());
	public function closeConnection();
	public function getRows($query);
	public function getRow($query);
	public function getValue($query);
	public function executeQuery($query);
	public function getLastInsertedId();
	public function quote($s);
	public function getTablePrefix();
}
