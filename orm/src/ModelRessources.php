<?php

namespace ORM;

class ModelRessources {
	private static $definedModels = array(
		\Model\User::class,
		\Model\Usergroup::class,
	);

	public static function getDefinedModels() {
		return self::$definedModels; 
	}
}
