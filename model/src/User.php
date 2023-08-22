<?php

namespace Model;

use Core\BaseModel;
use Core\Database\ORMMeta;

class User implements BaseModel {

	public function defineORM(): ORMMeta
	{
		$meta = new ORMMeta();
		$meta->tablename = "users";
		// $meta->columns = [
		// 	// TU!: schauen, ob man Instanziierung und prop assigning in eins machen kann, sonst xml definition und nicht in einer model.php
		// 	new Column()->setName("user"),
		// ]
		$meta->columns = [
			"username" => "VARCHAR(30)",
			"password" => "VARCHAR(30)",
			"email" => "VARCHAR(50)"
		];  

		return $meta;
	}
}
