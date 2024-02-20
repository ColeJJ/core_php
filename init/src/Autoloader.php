<?php

namespace Init;

use ORM\ModelRessources;

class Autoloader {

	public static function registerClasses() {
		spl_autoload_register(function($class) {
			self::register($class);
		});

		$classes = ModelRessources::getDefinedModels();

		foreach ($classes as $class) {
			self::register($class);
		}

	}	

	private static function register($class) {
		$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		$class_src = substr_replace($class, "src/", strpos($class, '/') + 1, 0);
		$class_src = __DIR__.'/../../'.$class_src.'.php';

		if(file_exists($class_src)){
			require $class_src;
			return;
		}
	}
}
