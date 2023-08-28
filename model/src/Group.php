<?php

namespace Model;

use Core\BaseModel;
use Core\Database\ORMMeta;
use Core\Database\SQL;

class Group implements BaseModel {

	public function defineORM(): ORMMeta
	{
		$meta = new ORMMeta();
		$meta->tablename = "usergroups";
		$meta->columns = [
			"groupname" => SQL::VARCHAR30,
		];

		return $meta;
	}
}
