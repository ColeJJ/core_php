<?php

use API\Router;

include_once __DIR__."/../../init/init.php";

$url  = explode("/", $_SERVER['REQUEST_URI']);
$obj = $url[2];
$requestedEndpoint = $url[3];
$method = $_SERVER['REQUEST_METHOD'];
$router = new Router();
$endpointClass = $router->findClass($obj, $requestedEndpoint);
var_dump($endpointClass);
?>
