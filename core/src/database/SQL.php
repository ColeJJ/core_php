<?php

namespace Core\Database;

class SQL {
	// Data types
	public const VARCHAR30 = "VARCHAR(30)";
	public const VARCHAR50 = "VARCHAR(50)";
	public const INT60 = "INT(60)";
	public const INT60_UNSIGNED = "INT(60) UNSIGNED";

	// column properties
	public const CONSTRAINT_TYPE_UNIQUE = "UNIQUE";
	public const CONSTRAINT_TYPE_FK = "FOREIGN KEY";
}
