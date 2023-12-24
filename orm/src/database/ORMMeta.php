<?php

namespace ORM\Database;

class ORMMeta {
	public ?string $tablename = null;
	public ?array $columns = null;
	public ?array $unique = null;
	public ?array $notNull = null;
	public ?array $fk = null;
}
