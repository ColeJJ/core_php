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
			"username" => "string",
			"password" => "string",
			"email" => "string"
		];  

		return $meta;
	}

}
