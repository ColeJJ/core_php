<?php

// this will be the the initial file to load 

use Core\Autoloader;
use Core\Database;
use Model\User;

// Autoloader
$loader = new Autoloader();
$loader->registerClasses();

// Database
new Database();
