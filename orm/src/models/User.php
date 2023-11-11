<?php

namespace ORM\Models;

use ORM\BaseModel;
use ORM\Database\ORMMeta;
use ORM\Database\SQL;

class User implements BaseModel {

	public function defineORM(): ORMMeta
	{
		$meta = new ORMMeta();
		$meta->tablename = "users";
		$meta->columns = [
			"username" => SQL::VARCHAR30,
			"password" => SQL::VARCHAR30,
			"email" => SQL::VARCHAR30,
			"groupID" => SQL::INT60_UNSIGNED,
			"groupID4" => SQL::INT60_UNSIGNED,
		];
		$meta->unique = ["email"];
		$meta->notNull = ["username", "password"]; 
		$meta->fk = [
			"groupID" => ["tablename" => "usergroups", "column" => "id"], 
		];

		return $meta;
	}
}
