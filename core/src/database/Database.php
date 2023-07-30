<?php

namespace Core\Database;

use Config\Config;
use Exception;
use mysqli;

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
		$this->db = new mysqli(Config::$DB_HOST, Config::$DB_USER, Config::$DB_PASSWORD, Config::$DB_DB);
		if($this->db->connect_error) {
			throw new Exception("Could not connect to mysql db.", 1);
		}
	}
}
