<?php

use \Lib\Route as Route;
use Enums\Enum as Enum;

$router = new Route\Router();

$router->pushRoute(new Route\RouteLink('news/', 'Article', 'index', Enum\RequestType::GET));
$router->pushRoute(new Route\RouteLink('news/:id', 'Article', 'show', Enum\RequestType::GET));
$router->pushRoute(new Route\RouteLink('news/', 'Article', 'create', Enum\RequestType::POST));
$router->pushRoute(new Route\RouteLink('news/:id', 'Article', 'update', Enum\RequestType::PUT));
$router->pushRoute(new Route\RouteLink('news/:id', 'Article', 'delete', Enum\RequestType::DELETE));
$router->pushRoute(new Route\RouteLink('users/:id/comments/:commentId', 'User', 'getUserComments', Enum\RequestType::GET));

$router->match();
?>