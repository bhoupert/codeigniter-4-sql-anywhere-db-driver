# codeigniter-4-sql-anywhere-db-driver

[![GitHub license](https://img.shields.io/badge/licence-GPL-blue)](https://github.com/bhoupert/codeigniter-4-sql-anywhere-db-driver/blob/main/LICENSE)

SAP SQL Anywhere Database driver for CodeIgniter 4 (based on version 4.3.0, for PHP 7.4+)
It allows you to use a SAP SQL Anywhere database in your CodeIgniter project.

## Prerequisites ##

You need to install SQL Anywhere PHP extension (https://wiki.scn.sap.com/wiki/display/SQLANY/The+SAP+SQL+Anywhere+PHP+Module) on your server to use this library.
You'll find more information on how to enable it here :
https://wiki.scn.sap.com/wiki/display/SQLANY/Getting+Started+with+SAP+SQL+Anywhere+and+PHP

## Information ##
This driver has been forked from SQL Server driver (some features cannot be used in SQL Anywhere).
The SQL Anywhere PHP API Reference is available on https://help.sap.com/docs/SAP_SQL_Anywhere/98ad9ec940e2465695685d98e308dff5/3bdef5c06c5f1014af9cfb6f6bd5016a.html

## Installation

### Step 1 - Add SQL Anywhere DB Driver

#### With Git
Clone this project in your existing CI4 project to add **_/app/database/SASQLDbDriver/_** folder.

#### With Composer
Run composer to add repository`.
```
composer require bhoupert/codeigniter-4-sql-anywhere-db-driver
composer update
```
Then open  **_app/Config/Autoload.php_** and add the new _App\Database\SASQLDbDriver_ namespace :
```php
public $psr4 = [
    APP_NAMESPACE                => APPPATH, // For custom app namespace
    'Config'                     => APPPATH . 'Config',
    'App\Database\SASQLDbDriver' => ROOTPATH . 'vendor/bhoupert/codeigniter-4-sql-anywhere-db-driver/app/Database/SASQLDbDriver',
];
```

### Step 2 - Add specific parameters
Edit **_/app/Config/Database.php_** file to add both _engine_ and _conAuth_ parameters in _$default_ var.
```php
public array $default = [
'DSN'      => '',
'hostname' => 'localhost',
'engine'   => 'my_engine_name',
'conAuth'  => 'Company=xxx_companyName_xxx;Application=xxx_application_xxx;Signature=__signature_hash__',
'username' => 'db_username',
'password' => 'db_password',
'database' => 'my_database',
'DBDriver' => 'App\Database\SASQLDbDriver',
'DBPrefix' => '',
'pConnect' => false,
'DBDebug'  => true,
'charset'  => 'utf8',
'DBCollat' => 'utf8_general_ci',
'swapPre'  => '',
'encrypt'  => false,
'compress' => false,
'strictOn' => false,
'failover' => [],
'port'     => 2630,
];
```

If you want to run PHPUnit database tests, you'll also need to add the same two parameters in _$tests_ var.
```php
public array $tests = [
'DSN'         => '',
'hostname'    => '127.0.0.1',
'engine'      => 'my_engine_name',
'conAuth'     => 'Company=xxx_companyName_xxx;Application=xxx_application_xxx;Signature=__signature_hash__',
'username'    => 'db_username',
'password'    => 'db_password',
'database'    => 'my_database',
'DBDriver'    => 'App\Database\SASQLDbDriver',
'DBPrefix'    => '',
'pConnect'    => false,
'DBDebug'     => true,
'charset'     => 'utf8',
'DBCollat'    => 'utf8_general_ci',
'swapPre'     => '',
'encrypt'     => false,
'compress'    => false,
'strictOn'    => false,
'failover'    => [],
'port'        => 2630,
'foreignKeys' => true,
'busyTimeout' => 1000,
];
```

_Some parameters can obviously be removed as they are not needed for SQL Anywhere connection._

### Step 3 (optional) - Add connection parameters in .env
Edit **_/app/Config/Database.php_** file to add your connection parameters as follows. The two specific parameters (_engine_ and _conAuth_) will be loaded by the driver and used to connect the SQL Anywhere database.
```ini
database.default.hostname = 127.0.0.1
database.default.engine   = my_engine_name
database.default.database = my_database
database.default.username = db_username
database.default.password = db_password
database.default.DBDriver = App\Database\SASQLDbDriver
database.default.port     = 2630
database.default.conAuth  = 'Company=xxx_companyName_xxx;Application=xxx_application_xxx;Signature=__signature_hash__',
```

## Some usage examples ##
Some examples are based on manual connection (https://codeigniter.com/user_guide/database/connecting.html) :
```php
$db = \Config\Database::connect();
```
Other examples are based on implicit connection :
```php
$this->db->...
```

### Basic queries ###
```php
$db = \Config\Database::connect();
$query = $db->table('USERS')->get();

echo '<pre>';
print_r($query->getResult());
echo '</pre>';
```

```php
$db = \Config\Database::connect();
$db->where('USER_GROUP', 'admin');
$this->db->order_by('NAME', 'desc');
$query = $db->get('USERS');
if ($db->query($sql)) {
    echo 'Success!';
} else {
    echo 'Query failed!';
}
```

### Limit / Order by ###
```php
$query = $this->db->table('USERS')
                  ->select('*')
                  ->limit(3, 1)
                  ->orderBy('USER_GROUP', 'desc')
                  ->get();
```

### dataSeek() ###
```php
$query = $this->db->query('select * from USERS;');

$query->dataSeek(4); // Skip the first 5 rows
$row = $query->getUnbufferedRow();

var_dump($row);
```

### Insert ###
```php
$this->db->table('USERS')->insert([
    'USER_ID'       => 'jDoe2',
    'NAME'          => 'Jane DOE',
    'USER_GROUP'    => 'user'
]);

echo 'Insert ID : ' . $this->db->insertID() . ' (' . $this->db->affectedRows() .' affected row(s))';
```

### $db->countAll() ###
```php
echo 'Number of rows in USERS table : ' . $this->db->table('USERS')->countAll();
```

### $db->$db->countAllResults() ###
```php
echo 'Number of "admin" users in USERS table : ' . $this->db->table('USERS')->where('USER_GROUP', 'admin')->countAllResults();
```

### Prepared queries ###

```php
$pQuery = $this->db->prepare(static function ($db) {
    $sql = 'insert into USERS (USER_ID, NAME, USER_GROUP) VALUES (?, ?, ?)';
    return (new Query($db))->setQuery($sql);
});

$result = $pQuery->execute(
    'jDoe',
    'John DOE',
    'admin'
);

var_dump($result);


$pQuery = $this->db->prepare(static function ($db) {
    return $db->table('USERS')->insert([
        'USER_ID'       => '',
        'NAME'          => '',
        'USER_GROUP'    => ''
    ]);
});
$userId     = 'jSmith';
$userName   = 'John SMITH';
$userGroup  = 'user';

$results = $pQuery->execute($userId, $userName, $userGroup);
```

### Debug ###
```php
$query      = $this->db->getLastQuery();
$microtime  = $query->getDuration();
echo 'Last query : ' . $query . ' (' . $microtime . ')<br />';
echo 'SQL Anywhere database version : ' . $this->db->getVersion();
```

### Also available ###
```php
var_dump($this->db->fieldExists('DUMMY_COL', 'USERS'));
var_dump($this->db->getFieldNames('USERS'));
var_dump($this->db->tableExists('USERS'));
var_dump($this->db->listTables());
var_dump($this->db->getFieldData('USERS'));
var_dump($this->db->getIndexData('USERS'));
var_dump($this->db->getForeignKeyData('USERS'));
```
