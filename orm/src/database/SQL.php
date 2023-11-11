<?php

namespace ORM\Database;

enum SQL_CONDIITON: string {
	case EQUAL = "=";
	case UNEQUAL = "!=";
}

class SQL {
	private string $sqlCommand;

	private function __construct()
	{
		return $this;	
	}

	// Data types
	public static const VARCHAR30 = "VARCHAR(30)";
	public static const VARCHAR50 = "VARCHAR(50)";
	public static const INT60 = "INT(60)";
	public static const INT60_UNSIGNED = "INT(60) UNSIGNED";

	// column properties
	public static const CONSTRAINT_TYPE_UNIQUE = "UNIQUE";
	public static const CONSTRAINT_TYPE_FK = "FOREIGN KEY";

	public function getSQL(): string {
		return $this->sqlCommand;
	}

	public function select(array $columns): SQL {
		$this->sqlCommand = "SELECT ";	
		foreach ($columns as $col) {
			$this->sqlCommand += $col + ', ';
		}

		return $this;
	}

	public function from(string $tablename): SQL {
		$this->sqlCommand = "FROM " + $tablename + " ";
		return $this;
	}

	public function where(string $column, SQL_CONDIITON $condition, $value): SQL {
		$this->sqlCommand += "WHERE $column $condition->value";

		if(is_string($value)) {
			$this->sqlCommand += "'" + $value + "' ";
		} else {
			$this->sqlCommand += "$value ";
		}
		return $this;
	}

	public function and(): SQL {
		$this->sqlCommand += "AND "; 
		return $this;
	}

	public function or(): SQL {
		$this->sqlCommand += "OR "; 
		return $this;
	}

	public function end(): SQL {
		$this->sqlCommand += ";";
		return $this;
	}
}
