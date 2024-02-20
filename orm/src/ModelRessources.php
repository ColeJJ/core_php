<?php

namespace ORM;

class ModelRessources {
	private static array $definedModels = array(
		\ORM\Models\User::class,
		\ORM\Models\UserGroup::class,
	);

	public static function getDefinedModels(): array {
		return self::$definedModels; 
	}
}
