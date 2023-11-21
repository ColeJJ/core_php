<?php

namespace ORM\Model;

use ORM\BaseModel;
use ORM\Database\ORMMeta;
use ORM\Database\SQL;

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
