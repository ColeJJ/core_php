<?php

use ORM\Models\User;

$request_method = $_SERVER['REQUEST_METHOD'];

// todo: we somehow need to require this here.. but i do not want that
require_once __DIR__."/../../init/init.php";

if ($request_method != 'GET') {
	throw new \Exception("This request method is not allowed: " . $request_method, 1);
}

$userModel = new User();
$resp = $userModel->getUsers();
var_dump($resp);
?>
