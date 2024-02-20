<?php

namespace ORM;

use ORM\Database\Database;
use ORM\Database\ORMMeta;
use ORM\Database\SQL;

abstract class Model {
	protected Database $db;
	public ORMMeta $meta;

	public function __construct()
	{
		$this->db = new Database();
		$this->defineORM();
	}

	public abstract function defineORM(): void;

	protected function get(string $tablename) {
		$sql = new SQL();
		$sql->select(['*'])->from($tablename)->end();
		$this->db->querySQL($sql->getSQL());
	}
}