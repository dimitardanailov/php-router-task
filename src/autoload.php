<?php

// Enums
require './enums/RequestType.php';
require './enums/ResponseError.php';

// Libs
require './libs/json/JsonHelper.php';

require_once './config/env.php';
require_once './config/database.php';

// Load Router

require './libs/routes/Router.php';
require './libs/routes/RouteLink.php';
require './config/routes.php';

