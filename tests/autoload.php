<?php

$base = str_replace('/tests', '', dirname(__FILE__));

require_once $base . '/enums/RequestType.php';
require_once $base . '/enums/ResponseError.php';

require_once $base . '/libs/json/JsonHelper.php';
require_once $base . '/libs/routes/RouteLink.php';

require_once $base . '/config/env.php';
require_once $base . '/config/database.php';
require_once $base . '/controllers/ArticleController.php';
