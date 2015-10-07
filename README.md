## Gelembjuk/DB package

PHP Package for DB access from PHP. It contains simple common interface and can be used with many DB engines. It is very simple alternative of PDO.

Use it to have simple and fast way to access a DB with an abstract layer. So you can change a DB engine later.

Currently only 2 engines are supported: mysql and mysqli (Gelembjuk\DB\MySQL and Gelembjuk\DB\MySQLi)

### Installation

Using composer: [gelembjuk/db](http://packagist.org/packages/gelembjuk/db) ``` require: {"gelembjuk/db": "*"} ```

### Configuration

List of options can differ depending on a DN engine. 

```php
$dbsettings = array(
	'engine' => 'mysql',
	'user' => 'dbuser',
	'password' => 'dbuserpassword',
	'database' => 'mydb',
	'host' => 'localhost',
	'connectioncharset' => 'utf8',
	'namescharset' => 'utf8'
	);

```

### Usage

```php

// composer autoloader
require '../vendor/autoload.php';

$socialnetwork = $_REQUEST['network'];  // this is one of: facebook, google, twitter, linkedin

// create social network login object. The second argument is array of API settings for a social network
$network = Gelembjuk\Auth\AuthFactory::getSocialLoginObject($socialnetwork,$integrations[$socialnetwork]);

$redirecturl = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/completelogin.php';

$socialauthurl = $network->getLoginStartUrl($redirecturl);
		
// remember the state. it will be used when complete a social login
$_SESSION['socialloginsate_'.$socialnetwork] = $network->serialize();

// this is optional. you can include a network name in your redirect url and then extract
$_SESSION['socialloginnetwork'] = $socialnetwork;

header("Location: $socialauthurl",true,301);
exit;

```


### Author

Roman Gelembjuk (@gelembjuk)

