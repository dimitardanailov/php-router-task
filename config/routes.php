<?php

use Lib\Route as Route;

$router = new Route\Router();

$router->pushRoute(new Route\RouteLink('news/:id', 'Article', 'index', \Enum\RequestType::GET));
$router->pushRoute(new Route\RouteLink('news', 'Article', 'create', \Enum\RequestType::POST));
$router->pushRoute(new Route\RouteLink('news/:id', 'Article', 'update', \Enum\RequestType::PUT));
$router->pushRoute(new Route\RouteLink('news/:id', 'Article', 'delete', \Enum\RequestType::DELETE));

$router->match();
?>