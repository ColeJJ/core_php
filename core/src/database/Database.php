<?php

namespace Core\Database;

use Config\Config; 
use Illuminate\Database\Capsule\Manager as Capsule;

class Database {
	private $db;

	public function __construct() {
		if(!$this->db) {
			$this->connect();
		}
	}

	public function getInstance() {
		return $this->db;
	}

	private function connect() {
		$capsule = new Capsule;

		$capsule->addConnection([
				'driver' => Config::$DB_DRIVER,
				'host' => Config::$DB_HOST,
				'database' => Config::$DB_DB,
				'username' => Config::$DB_USER,
				'password' => Config::$DB_PASSWORD,
				'charset' => Config::$DB_CHARSET,
				'collation' => Config::$DB_COLLATION,
				'prefix' => Config::$DB_PREFIX,
		]);

		$capsule->bootEloquent();
	}
}
