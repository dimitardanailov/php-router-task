<?php

require_once 'libs/database/Database.php';
require_once 'libs/database/DataValidation.php';

require_once 'models/Article.php';

\Lib\Database\Database::$config = array(
    'development' => array(
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'passwd',
        'database' => 'task-router',
        'encoding' => 'utf8',
    ),

    'production' => array(
        'host' => 'localhost',
        'user' => 'db_user',
        'password' => 'db_productuin',
        'database' => 'db',
        'encoding' => 'utf8',
    ),
);
?>
