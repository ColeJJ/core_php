<?php

// this will be the the initial file to load 

use core\Autoloader;

// Autoloader
$loader = new Autoloader();
$loader->registerClasses();

// Database
new Database();
