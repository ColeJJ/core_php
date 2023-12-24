<?php

namespace ORM\Database;

enum SQL_CONDIITON: string {
	case EQUAL = "=";
	case UNEQUAL = "!=";
}

class SQL {
	private string $sqlCommand;

	public function __construct()
	{
		return $this;	
	}

	// Data types
	public const VARCHAR30 = "VARCHAR(30)";
	public const VARCHAR50 = "VARCHAR(50)";
	public const INT60 = "INT(60)";
	public const INT60_UNSIGNED = "INT(60) UNSIGNED";

	// column properties
	public const CONSTRAINT_TYPE_UNIQUE = "UNIQUE";
	public const CONSTRAINT_TYPE_FK = "FOREIGN KEY";

	public function getSQL(): string {
		return $this->sqlCommand;
	}

	public function select(array $columns): SQL {
		$this->sqlCommand = "SELECT ";	
		foreach ($columns as $col) {
			$this->sqlCommand .= $col . ', ';
		}

		$this->sqlCommand = rtrim($this->sqlCommand, ", ");
		$this->sqlCommand .= " ";
		return $this;
	}

	public function alter(string $tablename): SQL {
		$this->sqlCommand = "ALTER TABLE " . $tablename;	
		return $this;
	}

	public function from(string $tablename): SQL {
		$this->sqlCommand .= "FROM " . $tablename . " ";
		return $this;
	}

	public function where(string $column, SQL_CONDIITON $condition, $value): SQL {
		$this->sqlCommand .= "WHERE $column $condition->value ";

		if(is_string($value)) {
			$this->sqlCommand .= "'" . $value . "' ";
		} else {
			$this->sqlCommand .= "$value ";
		}
		return $this;
	}

	public function and(string $column, SQL_CONDIITON $condition, $value): SQL {
	$this->sqlCommand .= "AND $column $condition->value ";

		if(is_string($value)) {
			$this->sqlCommand .= "'" . $value . "' ";
		} else {
			$this->sqlCommand .= "$value ";
		}
		return $this;	
	}

	public function or(): SQL {
		$this->sqlCommand .= "OR "; 
		return $this;
	}

	public function addFk(array $fks, string $tablename): SQL {
		$fk_contraint_count = 1;
		foreach ($fks as $col => $value) {
			$this->sqlCommand = $this->sqlCommand . " ADD CONSTRAINT ". CONSTRAINT_PREFIXES::FK->value . $tablename . "_" . $fk_contraint_count .  
				" FOREIGN KEY ($col)" . 
				" REFERENCES " . $value["tablename"] . "(" . $value["column"] . "),";  
			$fk_contraint_count += $fk_contraint_count;
		}
		$this->sqlCommand = rtrim($this->sqlCommand, ",");
		return $this;
	}

	public function addUnique(string $tablename, array $uniqueCols): SQL {
		$this->sqlCommand .= " ADD CONSTRAINT unique_". $tablename . " UNIQUE ("; 

		foreach ($uniqueCols as $col) {
			$this->sqlCommand .= $col . ",";
		}

		$this->sqlCommand = rtrim($this->sqlCommand, ",");
		$this->sqlCommand= $this->sqlCommand. ")";

		return $this;
	}

	public function setNotNull(array $tableColumns, array $notNullColumns): SQL {
		$this->setNull($tableColumns, $notNullColumns);

		foreach ($notNullColumns as $col) {
			$this->sqlCommand .= " MODIFY COLUMN " . $col . " " . $tableColumns[$col] . " NOT NULL,";
		}

		$this->sqlCommand = rtrim($this->sqlCommand, ",");
		return $this;
	}

	// todo: instead of looping over every nullable columns, catch only not null colums which need to be set to nullabled 
	private function setNull(array $tableColumns, array $notNullColumns): void {
		$nullableColumns = $tableColumns;
		foreach ($notNullColumns as $col) {
			unset($nullableColumns[$col]);
		}

		foreach ($nullableColumns as $col => $value) {
			$this->sqlCommand .= " MODIFY COLUMN " . $col . " " . $value . " NULL,";
		}
	}

	public function end(): SQL {
		$this->sqlCommand = rtrim($this->sqlCommand, " ");
		$this->sqlCommand .= ";";
		return $this;
	}
}
