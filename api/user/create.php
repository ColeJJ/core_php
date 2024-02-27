<?php

use ORM\Models\User;

require_once __DIR__."/../../init/init.php";

$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method != 'POST') {
	throw new \Exception("This request method is not allowed: " . $request_method, 1);
}

$params = $_REQUEST;

$user = new User();
$user->username = $params['username'];
$user->password = $params['password'];
$user->email = $params['email'];

echo $user->save();
?>
