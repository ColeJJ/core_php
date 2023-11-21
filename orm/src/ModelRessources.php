<?php

namespace ORM;

class ModelRessources {
	private static $definedModels = array(
		\ORM\Model\User::class,
		\ORM\Model\Usergroup::class,
	);

	public static function getDefinedModels() {
		return self::$definedModels; 
	}
}
