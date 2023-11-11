<?php

namespace ORM\Models;

use Core\BaseModel;
use Core\Database\ORMMeta;
use Core\Database\SQL;

class Usergroup implements BaseModel {

	public function defineORM(): ORMMeta
	{
		$meta = new ORMMeta();
		$meta->tablename = "usergroups";
		$meta->columns = [
			"test" => SQL::VARCHAR30,
		];

		return $meta;
	}
}
