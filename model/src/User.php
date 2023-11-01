<?php

namespace Model;

use Core\BaseModel;
use Core\Database\ORMMeta;
use Core\Database\SQL;

class User implements BaseModel {

	public function defineORM(): ORMMeta
	{
		$meta = new ORMMeta();
		$meta->tablename = "users";
		// TU?: SQL Class with consts providing this data classes
		$meta->columns = [
			"username" => SQL::VARCHAR30,
			"password" => SQL::VARCHAR30,
			"email" => SQL::VARCHAR50,
			"groupID" => SQL::INT60_UNSIGNED,
			"groupID2" => SQL::INT60_UNSIGNED,
		];
		$meta->unique = ["email"];
		$meta->notNull = ["username", "password"]; 
		$meta->fk = [
			"groupID" => ["tablename" => "usergroups", "column" => "id"], 
			"groupID2" => ["tablename" => "usergroups", "column" => "id"] 
		];

		return $meta;
	}
}
