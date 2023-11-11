<?php

use ORM\BaseModel;
use ORM\Database\Database;
use ORM\ModelRessources;
 
require_once __DIR__."/../init/init.php";

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
