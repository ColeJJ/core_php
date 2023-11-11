<?php

// this will be the the initial file to load 

use Init\Autoloader;
use ORM\Database\Database;

// Autoloader
Autoloader::registerClasses();

// Database
new Database();
