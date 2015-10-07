## Gelembjuk/DB package

PHP Package for DB access from PHP. It contains simple common interface and can be used with many DB engines. It is very simple alternative of PDO.

Use it to have simple and fast way to access a DB with an abstract layer. So you can change a DB engine later.

Currently only 2 engines are supported: mysql and mysqli (Gelembjuk\DB\MySQL and Gelembjuk\DB\MySQLi)

### Installation

Using composer: [gelembjuk/db](http://packagist.org/packages/gelembjuk/db) ``` require: {"gelembjuk/db": "*"} ```

### Usage

```php

require ('vendor/autoload.php');

// simple example to show how to work with 2 DB parelelly in a PHP script

$dbsettings1 = array(
	'user' => 'dbuser',
	'password' => 'dbuserpassword',
	'database' => 'mydb',
	'host' => 'localhost',
	'connectioncharset' => 'utf8',
	'namescharset' => 'utf8'
	);

// different DB
$dbsettings2 = array(
	'user' => 'dbuser',
	'password' => 'dbuserpassword',
	'database' => 'mydb2',
	'host' => 'localhost',
	'connectioncharset' => 'utf8',
	'namescharset' => 'utf8'
	);

$dbengine1 = new Gelembjuk\DB\MySQL($dbsettings1);
$dbengine2 = new Gelembjuk\DB\MySQLi($dbsettings2);
// connection will be established on a first request to a DB

class MyTable extends Gelembjuk\DB\Base {
	public function getUsers() {
		return $this->getRows('SELECT * FROM users');
	}
	public function addUser($name,$email) {
		$sql = "INSERT INTO users (name,email) VALUES ".
			"('".$this->quote($name)."','".$this->quote($email)."')";
		$this->executeQuery();
		
		return getLastInsertedId();
	}
}

$mytable = new MyTable($dbengine1);

$userid1 = $mytable->addUser('User 1','email@gmail.com');
$userid2 = $mytable->addUser('User 2','email2@gmail.com');

print_r($mytable->getUsers());

// this will do same but with different DB
$mytable2 = new MyTable($dbengine2);

print_r($mytable2->getUsers());

```


### Author

Roman Gelembjuk (@gelembjuk)

