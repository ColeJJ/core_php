<?php

use Core\BaseModel;
use Core\Database\Database;
use Model\ModelRessources;
 
require_once __DIR__."/../../init.php";

$modelClasses = ModelRessources::getDefinedModels();

foreach ($modelClasses as $class) {
	/**
	 * @var BaseModel 
	*/
	$model = new $class;
	$modelMeta = $model->defineORM();

	$tablename = $modelMeta->tablename;
	$columns = $modelMeta->columns;

	Database::runOrm($modelMeta);
}
