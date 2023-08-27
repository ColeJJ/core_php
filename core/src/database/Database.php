<?php

namespace Core\Database;

use Config\Config;
use Exception;
use mysqli;

class Database {
	private static $db = null;

	public function __construct() {
		if(!self::$db) {
			$this->connect();
		}
	}

	private static function connect() {
		self::$db = new mysqli(Config::$DB_HOST, Config::$DB_USER, Config::$DB_PASSWORD, Config::$DB_DB);
		if(self::$db->connect_error) {
			throw new Exception("Could not connect to mysql db.", 1);
		}
	}

	public static function getInstance() {
		if (self::$db == null) {
			self::$db = self::connect();
		}

		return self::$db;
	}

	public static function runOrm(ORMMeta $meta) {
		self::createTableWithMetadata($meta);
		self::setUniqueColumns();
	}

	public static function createTableWithMetadata(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$columns = $meta->columns;

		// TU?: call mysql function to create table with tablename and columns 
		// TU?: hier noch DB Transaktion start? 
		$sql = "CREATE TABLE ".$tablename." (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";

		foreach ($columns as $column => $dataType) {
			$sql = $sql . ", ";
			$sql = $sql . $column . " " . $dataType . " NOT NULL";
		}

		$sql = $sql . ")";

		if (self::$db->query($sql) === TRUE) {
			echo "Table created successfully";
		} else {
			echo "Error creating table: " . self::$db->error;
		}

		// TU?: hier noch DB Transaktion ende? 
	}

	// TU!
	public static function setUniqueColumns()

}
