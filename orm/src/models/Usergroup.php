<?php

namespace ORM\Models;

use ORM\Database\ORMMeta;
use ORM\Database\SQL;
use ORM\Model;

class Usergroup extends Model {

	public function defineORM(): void 
	{
		$meta = new ORMMeta();
		$meta->tablename = "usergroups";
		$meta->columns = [
			"test" => SQL::VARCHAR30,
		];
	}

	public function getModelAttributesAsArray(): array
	{
		// todo
		return [];
	}
}
