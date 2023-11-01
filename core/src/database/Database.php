<?php

namespace Core\Database;

use Config\Config;
use Exception;
use mysqli;

enum CONSTRAINT_PREFIXES: string {
	case UNIQUE = "unique_";
	case FK = "fk_";
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
		self::setNotNullColumn($meta);
		self::setFks($meta);
	}

	public static function createTableWithMetadata(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$columns = $meta->columns;

		// TU?: call mysql function to create table with tablename and columns 
		// TU?: hier noch DB Transaktion start? 
		$sql = "CREATE TABLE IF NOT EXISTS ".$tablename." (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";

		foreach ($columns as $column => $dataType) {
			$sql = $sql . ", ";
			$sql = $sql . $column . " " . $dataType;
		}

		$sql = $sql . ")";

		if (self::$db->query($sql) === TRUE) {
			echo "Table ". $tablename . " created or updated successfully\n";
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
		self::deleteConstraintIfGiven($tablename, CONSTRAINT_PREFIXES::UNIQUE, SQL::CONSTRAINT_TYPE_UNIQUE);
		
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

	public static function setNotNullColumn(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$columns = $meta->columns;
		$notNullColumns = $meta->notNull;

		if(!self::checkColumnsExist($meta->columns, $notNullColumns)) {
			echo "Determined not nullable column not given in table: ". $tablename . ".";
			return;
		};

		if($notNullColumns && count($notNullColumns) > 0) {
			$nullColumns = $columns;
			foreach ($notNullColumns as $col) {
				unset($nullColumns[$col]);
			}

			// add new updated unique constraint
			$sql = "ALTER TABLE ". $tablename . " "; 

			foreach ($notNullColumns as $col) {
				// TU: PostgreSQL Query sieht so aus: ALTER COLUMN spaltenname SET NOT NULL; 
				$sql = $sql . "MODIFY COLUMN " . $col . " " . $columns[$col] . " " . "NOT NULL,";
			}

			foreach ($nullColumns as $col => $value) {
				// TU: PostgreSQL Query sieht so aus: ALTER COLUMN spaltenname DROP NOT NULL; 
				$sql = $sql . "MODIFY COLUMN " . $col . " " . $value . " NULL,";
			}
		
			$sql = rtrim($sql, ",");
			$sql = $sql . ";";

			if (self::$db->query($sql) === TRUE) {
				echo "Set nullable columns successfully\n";
			} else {
				echo "Error setting nullable columns: " . self::$db->error;
			}
		}
	}

	public static function setFks(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$fks = $meta->fk;

		if(!$fks) {
			return;
		}

		if(!self::checkColumnsExist($meta->columns, array_keys($fks))) {
			echo "Determined fk column not given in table: ". $tablename . ".";
			return;
		};

		self::deleteConstraintIfGiven($tablename, CONSTRAINT_PREFIXES::FK, SQL::CONSTRAINT_TYPE_FK);
		
		if(count($fks) > 0) {
			// add new updated unique constraint
			$sql = "ALTER TABLE ". $tablename; 

			$fk_contraint_count = 1;
			// SQL: ALTER TABLE aa
			// ADD CONSTRAINT fk_aa_to_bb
			// FOREIGN KEY (a)
			// REFERENCES bb(ID),
			// ADD CONSTRAINT fk_aa_to_bb2
			// FOREIGN KEY (b)
			// REFERENCES bb(ID2);
			foreach ($fks as $col => $value) {
				$sql = $sql . " ADD CONSTRAINT ". CONSTRAINT_PREFIXES::FK->value . $tablename . "_" . $fk_contraint_count .  
				" FOREIGN KEY ($col)" . 
				" REFERENCES " . $value["tablename"] . "(" . $value["column"] . "),";  
				$fk_contraint_count = $fk_contraint_count++;
			}
			
			$sql = rtrim($sql, ",");
			$sql = $sql . ";";

			if (self::$db->query($sql) === TRUE) {
				echo "Set fk contraint(s) successfully\n";
			} else {
				echo "Error creating fk constraint(s): " . self::$db->error;
			}
		}
	}

	private static function deleteConstraintIfGiven(string $tablename, CONSTRAINT_PREFIXES $contraint_prefix, string $constraint_type) {
		$constraint_sql = "SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = '$constraint_type' AND TABLE_NAME = '$tablename' AND TABLE_SCHEMA = '". Config::$DB_DB."'";
		$foundConstraints = self::$db->query($constraint_sql, MYSQLI_USE_RESULT)->fetch_assoc();
		$constraintName = $foundConstraints['CONSTRAINT_NAME'];
		$constraintType = $foundConstraints['CONSTRAINT_TYPE'];

		if ($constraintName) {
			if($constraintType === SQL::CONSTRAINT_TYPE_FK) {
				$delete_sql = "ALTER TABLE ". $tablename . " DROP FOREIGN KEY " . $constraintName .";";	
			} else {
				$delete_sql = "ALTER TABLE ". $tablename . " DROP KEY " . $constraintName .";";	
			}
			if(self::$db->query($delete_sql) === TRUE) {
				echo "Deleted unique constraint successfully\n";
			} else {
				echo "Error deleting constraint: " . self::$db->error;
			}
		}
	}

	private static function checkSingleColumnExist(array $columns, string $column) {
		return in_array($column, array_keys($columns));
	}

	private static function checkColumnsExist(array $columns, array|null $needleColumns): bool {
		if($needleColumns) {
			foreach ($needleColumns as $col) {
				if(!self::checkSingleColumnExist($columns, $col)) {
					echo "Column ". $col . " not found. ";
					return false;
				}
			}
		}

		return true;
	}
}
