<?php

namespace Init;

class Autoloader {

	public static function registerClasses() {
		spl_autoload_register(function($class) {
			$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			$class_src = substr_replace($class, "src/", strpos($class, '/') + 1, 0);
			$class_src = __DIR__.'/../../'.$class_src.'.php';

			if(file_exists($class_src)){
				require $class_src;
				return;
			}
		});

		self::registerPackages();
	}	

	private static function registerPackages() {
		require_once __DIR__."./../../vendor/autoload.php";
	} 
}
