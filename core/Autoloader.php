<?php

namespace core;

class Autoloader {

	public function registerClasses() {
		spl_autoload_register(function($class) {
			require $class.'php';
		});
	}	
}
