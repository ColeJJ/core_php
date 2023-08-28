<?php

namespace Model;

use Core\BaseModel;
use Core\Database\ORMMeta;

class User implements BaseModel {

	public function defineORM(): ORMMeta
	{
		$meta = new ORMMeta();
		$meta->tablename = "users";
		$meta->columns = [
			"username" => "VARCHAR(30)",
			"password" => "VARCHAR(30)",
			"email" => "VARCHAR(50)"
		];  
		$meta->unique = ["email"];

		return $meta;
	}
}
