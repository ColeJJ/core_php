<?php

use ORM\Database\Database;
use ORM\ModelRessources;

require_once __DIR__."/../init/init.php";

$modelClasses = ModelRessources::getDefinedModels();

foreach ($modelClasses as $class) {
	/**
	 * @var Model $model 
 	*/
	$model = new $class;
	$model->defineORM();
	$model->meta->tablename;
	$model->meta->columns;

	Database::runOrm($model->meta);
}
