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

	private static function getColumnsOfTable(string $tablename): array {
		$sql = "
			SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = '". Config::$DB_DB . "' 
			AND TABLE_NAME =  '$tablename'";
		$dbColumns = self::$db->query($sql)->fetch_all(MYSQLI_ASSOC);
		$dbColumns = array_map(function ($column){
			return $column['COLUMN_NAME'];
		}, $dbColumns);
		return $dbColumns;
	}

	private static function getDeletableColumns(string $tablename, array $columns) {
		$dbColumns = self::getColumnsOfTable($tablename);
		$compareCols = array_keys($columns);
		array_push($compareCols, 'id');
		$deletableCols = array_diff($dbColumns, $compareCols);
		return $deletableCols;
	}

	public static function getInstance() {
		if (self::$db == null) {
			self::$db = self::connect();
		}

		return self::$db;
	}

	public static function runOrm(ORMMeta $meta) {
		self::syncTable($meta);
		self::setUniqueColumns($meta);
		self::setNotNullColumn($meta);
		self::setFks($meta);
	}

	public static function syncTable(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$columns = $meta->columns;

		$createSQL = "CREATE TABLE IF NOT EXISTS ".$tablename." (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY);";

		if (self::$db->query($createSQL)) {
			echo "Checked and created Table ". $tablename . " successfully if not exists.\n";
		}

		// compare columns in DB with $meta columns -> delete alle, die nicht enthalten sind
		$deleteColumns = self::getDeletableColumns($tablename, $columns);
		if($deleteColumns) {
			$deleteSQL = "ALTER TABLE $tablename DROP COLUMN";
			foreach ($deleteColumns as $col) {
				$deleteSQL = $deleteSQL . " $col,";
			}
			$deleteSQL = rtrim($deleteSQL, ",");
			$deleteSQL = $deleteSQL . ";";
			if(self::$db->query($deleteSQL)) {
				echo "Not anymore exisiting Columns successfully deleted\n";
			}
		}

		foreach ($columns as $column => $colType) {
			$columnSQL = "SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tablename' AND TABLE_SCHEMA = '". Config::$DB_DB . "' AND COLUMN_NAME = '$column';";
			$foundColumn = self::$db->query($columnSQL)->fetch_assoc();
			$existingColName = $foundColumn['COLUMN_NAME'];
			$existingColType = $foundColumn['COLUMN_TYPE'];
			
			if (!$existingColName) {
				$createColumnSQL = "ALTER TABLE $tablename ADD $column $colType;";
				if(self::$db->query($createColumnSQL)) {
					echo "Column ". $column . " created successfully\n";
				}
				continue;
			}

			if($existingColName !== $column) {
				$renameColumnSQL = "ALTER TABLE ". $tablename . " RENAME COLUMN ". $column . " " . $foundColumn . " to " . $column .";"; 
				if(self::$db->query($renameColumnSQL)) {
					echo "Column name of successfully updated to: ". $column . "\n";
				}
			} 
			
			if($existingColType !== strtolower($colType)) {
				$updateTypeSQL = "ALTER TABLE ". $tablename . " MODIFY COLUMN ". $column . " " . $colType . ";";
				if(self::$db->query($updateTypeSQL)) {
					echo "Datatype of column ". $column . " successfully updated to: ". $colType. "\n";
				}
			} 
		}
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
			foreach ($fks as $col => $value) {
				$sql = $sql . " ADD CONSTRAINT ". CONSTRAINT_PREFIXES::FK->value . $tablename . "_" . $fk_contraint_count .  
				" FOREIGN KEY ($col)" . 
				" REFERENCES " . $value["tablename"] . "(" . $value["column"] . "),";  
				$fk_contraint_count += $fk_contraint_count;
			}
			
			$sql = rtrim($sql, ",");
			$sql = $sql . ";";

			if (self::$db->query($sql)) {
				echo "Set fk contraint(s) successfully\n";
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
