<?php
$host = 'localhost';
$database = 'task-router';
$user = 'root';
$password = 'passwd';
$encoding = 'utf8';


//define globals
define('HOST', $host);
define('DATABASE', $database);
define('USER', $user);
define('PASSWORD', $password);
define('ENCODING',$encoding);

require_once 'libs/database/Database.php';

require_once 'models/Article.php';
?>
