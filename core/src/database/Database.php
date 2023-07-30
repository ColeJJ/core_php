<?php

namespace Core\Database;

use Config\Config;
use Exception;
use mysqli;

class Database {
	// TU!: aktuell ist das hier Instanz basiert, ich will hier aber einen Singleton -> wir müssen die Logik hier noch iwie umbauen
	private $db;

	public function __construct() {
		if(!$this->db) {
			$this->connect();
		}
	}

	private function connect() {
		$this->db = new mysqli(Config::$DB_HOST, Config::$DB_USER, Config::$DB_PASSWORD, Config::$DB_DB);
		if($this->db->connect_error) {
			throw new Exception("Could not connect to mysql db.", 1);
		}
	}

	public function getInstance() {
		return $this->db;
	}

	public function createTable(ORMMeta $meta) {
		var_dump($this->db);
	}

}
