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
			"groupID" => SQL::INT60,
		];
		$meta->unique = ["email"];
		$meta->notNull = ["username", "password"]; 
		// TU!: setting fks columns
		$meta->fk = [
			"groupID" => ["tablename" => "usergroups", "column" => "ID"] 
		];

		return $meta;
	}
}
