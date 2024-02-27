<?php

namespace ORM\Database;

use Config\Config;
use ORM\Database\ORMMeta;
use Exception;
use mysqli;
use mysqli_result;
use ORM\Model;

enum CONSTRAINT_PREFIXES: string {
	case UNIQUE = "unique_";
	case FK = "fk_";
}

class Database {
	/**
	 * @var mysqli $db
	*/
	private static $db = null;
	private static $sql = null;

	public function __construct() {
		if(!self::$db) {
			self::connect();
			self::$sql = new SQL();
		}
	}

	private static function connect() {
		self::$db = new mysqli(Config::$DB_HOST, Config::$DB_USER, Config::$DB_PASSWORD, Config::$DB_DB);
		if(self::$db->connect_error) {
			throw new Exception("Could not connect to mysql db.", 1);
		}
	}

	// todo: security issue with this being public?
	public function querySQL(string $sql): mysqli_result | bool {
		return self::$db->query($sql);
	}

	private static function query($resultMode = MYSQLI_STORE_RESULT): mysqli_result | bool {
		$sqlCommand = self::$sql->getSQL();
		return self::$db->query($sqlCommand, $resultMode);
	}

	public function save(Model $model, string $tablename, array $cols): bool {
		self::$sql
			->insert($tablename, $cols, $model->getModelAttributesAsArray())
			->end();

		if (self::query()) {
			return true;
		}

		return false;
	}

	// ORM

	private static function getColumnsOfTable(string $tablename): array {
		self::$sql
			->select(['COLUMN_NAME'])
			->from('INFORMATION_SCHEMA.COLUMNS')
			->where('TABLE_SCHEMA', SQL_CONDIITON::EQUAL, Config::$DB_DB)
			->and('TABLE_NAME', SQL_CONDIITON::EQUAL, $tablename)
			->end();

		$dbColumns = self::query()->fetch_all(MYSQLI_ASSOC);
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
			self::$sql
				->select(['COLUMN_NAME', 'COLUMN_TYPE'])
				->from('INFORMATION_SCHEMA.COLUMNS')
				->where('TABLE_NAME', SQL_CONDIITON::EQUAL, $tablename)
				->and('TABLE_SCHEMA', SQL_CONDIITON::EQUAL, Config::$DB_DB)
				->and('COLUMN_NAME', SQL_CONDIITON::EQUAL, $column)
				->end();

			$foundColumn = self::query()->fetch_assoc();

			if (!$foundColumn) {
				self::$sql
					->alter($tablename)
					->addColumn($column, $colType)
					->end();

				if(self::query() === TRUE) {
					echo "Column ". $column . " created successfully\n";
				}
				continue;
			}

			if ($foundColumn && $foundColumn['COLUMN_TYPE'] !== strtolower($colType)) {
				self::$sql
					->alter($tablename)
					->changeColType($column, $colType)
					->end();

				if(self::query()) {
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

		self::deleteConstraintsIfGiven($tablename, SQL::CONSTRAINT_TYPE_UNIQUE);
		
		if($uniqueCols) {
			self::$sql
				->alter($tablename)
				->addUnique($tablename, $uniqueCols)
				->end();

			if (self::query() === TRUE) {
				echo "Set unique contraint successfully\n";
			} else {
				echo "Error creating unique constraint: " . self::$db->error;
			}
		}
	}

	public static function setNotNullColumn(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$tableColumns = $meta->columns;
		$notNullColumns = $meta->notNull === null ? [] : $meta->notNull;


		if(!self::checkColumnsExist($meta->columns, $notNullColumns)) {
			echo "Determined not nullable column not given in table: ". $tablename . ".";
			return;
		};

		if (self::query() === TRUE) {
			echo "Set nullable columns successfully\n";
		} else {
			echo "Error setting nullable columns: " . self::$db->error;
		}
	}

	public static function setFks(ORMMeta $meta) {
		$tablename = $meta->tablename;
		$fks = $meta->fk;

		self::deleteConstraintsIfGiven($tablename, SQL::CONSTRAINT_TYPE_FK);

		if($fks) {
			if(!self::checkColumnsExist($meta->columns, array_keys($fks))) {
				echo "Determined fk column not given in table: ". $tablename . ".";
				return;
			};

			self::$sql->alter($tablename)->addFk($fks, $tablename)->end();

			if (self::query() === TRUE) {
				echo "Set fk contraint(s) successfully\n";
			}
		}
	}

	private static function deleteConstraintsIfGiven(string $tablename, string $constraint_type) {
		self::$sql
			->select(['CONSTRAINT_NAME', 'CONSTRAINT_TYPE'])
			->from('INFORMATION_SCHEMA.TABLE_CONSTRAINTS')
			->where('CONSTRAINT_TYPE', SQL_CONDIITON::EQUAL, $constraint_type)
			->and('TABLE_NAME', SQL_CONDIITON::EQUAL, $tablename)
			->and('TABLE_SCHEMA', SQL_CONDIITON::EQUAL, Config::$DB_DB)
			->end();

		$foundConstraints = self::query(MYSQLI_USE_RESULT)->fetch_all(MYSQLI_ASSOC);

		if ($foundConstraints) {
			foreach ($foundConstraints as $constraint) {
				$constraintName = $constraint['CONSTRAINT_NAME'];
				$constraintType = $constraint['CONSTRAINT_TYPE'];

				if($constraintType === SQL::CONSTRAINT_TYPE_FK) {
					$delete_sql = "ALTER TABLE ". $tablename . " DROP FOREIGN KEY " . $constraintName .";";	
				} else {
					$delete_sql = "ALTER TABLE ". $tablename . " DROP KEY " . $constraintName .";";	
				}

				if(self::$db->query($delete_sql) === TRUE) {
					echo "Deleted constraint '$constraintName' successfully\n";
				}
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
