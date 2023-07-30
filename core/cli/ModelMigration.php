<?php

use Model\ModelRessources;
 
require_once __DIR__."/../../init.php";

$modelClasses = ModelRessources::getDefinedModels();

foreach ($modelClasses as $class) {
	/**
	 * @var BaseModel
	*/
	$model = new $class;
	$modelMeta = $model->defineORM();

	var_dump($modelMeta);
}
