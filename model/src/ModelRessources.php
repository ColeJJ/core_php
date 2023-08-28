<?php

namespace Model;

class ModelRessources {
	private static $definedModels = array(
		\Model\User::class,
		\Model\Group::class,
	);

	public static function getDefinedModels() {
		return self::$definedModels; 
	}
}
