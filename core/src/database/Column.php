<?php

namespace Core\Database; 

// TU: use enum for dataType strings
// enum DATA_TYPE {
// 	$VARCHAR_30 => 'Varchar(30)'
// } 

class Column {
	public string $name;
	public string $dataType;
	public bool $unique = false;
	public bool $nullable = false;

	public function setUnique() {
		$this->unique = true;
	}

	public function setNullable() {
		$this->nullable = true;
	}

	public function setDatatype($dataType) {
		$this->dataType = $dataType;
	}

	public function setName(string $name) {
		$this->name= $name;
	}
}
