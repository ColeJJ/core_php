<?php

namespace ORM\Models;

use ORM\Database\ORMMeta;
use ORM\Database\SQL;
use ORM\Model;

class User extends Model {
	public string $username;
	public string $password;
	public string $email;
	public int $groupID;

	public function defineORM(): void {
		$this->meta = new ORMMeta();
		$this->meta->tablename = "users";
		$this->meta->columns = [
			"username" => SQL::VARCHAR30,
			"password" => SQL::VARCHAR30,
			"email" => SQL::VARCHAR30,
			"groupID" => SQL::INT_UNSIGNED,
		];
		$this->meta->unique = ["email"];
		$this->meta->notNull = ["username", "password"]; 
		$this->meta->fk = [];
	}

	public function getModelAttributesAsArray(): array {
		return [
			'username' => $this->username ?? null,
			'password' => $this->password ?? null,
			'email' => $this->email ?? null,
			'groupID' => $this->groupID ?? null,
		];
	}

	public function getUsers() {
		$this->get($this->meta->tablename);
	}

	public function create() {
		// todo:
	}
}
