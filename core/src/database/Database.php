<?php

namespace Core\Database;

use Config\Config;
use Exception;
use mysqli;

enum CONSTRAINT_PREFIXES: string {
	case UNIQUE = "unique_";
}

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
		self::setUniqueColumns($meta);
	}

	public static function createTableWithMetadata(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$columns = $meta->columns;

		// TU?: call mysql function to create table with tablename and columns 
		// TU?: hier noch DB Transaktion start? 
		$sql = "CREATE TABLE IF NOT EXISTS ".$tablename." (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";

		foreach ($columns as $column => $dataType) {
			$sql = $sql . ", ";
			$sql = $sql . $column . " " . $dataType . " NOT NULL";
		}

		$sql = $sql . ")";

		if (self::$db->query($sql) === TRUE) {
			echo "Table created or updated successfully\n";
		} else {
			echo "Error creating or updating table: " . self::$db->error;
		}
		// TU?: hier noch DB Transaktion ende? 
	}

	public static function setUniqueColumns(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$uniqueCols = $meta->unique;
		
		if(!self::checkColumnsExist($meta->columns, $uniqueCols)) {
			echo "Determined unique column not given in table: ". $tablename . ".";
			return;
		};
		self::deleteConstraintIfGiven($tablename, CONSTRAINT_PREFIXES::UNIQUE);
		
		if($uniqueCols && count($uniqueCols) > 0) {
			// add new updated unique constraint
			$sql = "ALTER TABLE ". $tablename . " ADD CONSTRAINT unique_". $tablename . " UNIQUE ("; 

			foreach ($uniqueCols as $col) {
				$sql = $sql . $col . ",";
			}
			
			$sql = rtrim($sql, ",");
			$sql = $sql . ");";

			if (self::$db->query($sql) === TRUE) {
				echo "Set unique contraint successfully\n";
			} else {
				echo "Error creating unique constraint: " . self::$db->error;
			}
		}
	}

	private static function deleteConstraintIfGiven(string $tablename, CONSTRAINT_PREFIXES $contraint_prefix) {
		$constraint_sql = "SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'UNIQUE' AND TABLE_NAME = '$tablename' AND CONSTRAINT_NAME = 'unique_$tablename'";
		$foundConstraints = self::$db->query($constraint_sql);

		if ($foundConstraints && $foundConstraints->num_rows > 0) {
			$delete_sql = "ALTER TABLE ". $tablename . " DROP KEY " . $contraint_prefix->value . $tablename .";";	
			if(self::$db->query($delete_sql) === TRUE) {
				echo "Deleted unique constraint successfully\n";
			} else {
				echo "Error deleting constraint: " . self::$db->error;
			}
		}
	}

	private static function checkColumnExist(array $columns, string $column) {
		return in_array($column, $columns);
	}

	private static function checkColumnsExist(array $columns, array $needleColumns): bool {
		foreach ($needleColumns as $col) {
			# code...
			if(!self::checkColumnExist($columns, $col)) {
				echo "Column ". $col . " not found. ";
				return false;
			}
		}
		return true;
	}
}
