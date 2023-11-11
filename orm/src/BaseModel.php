<?php

namespace ORM;

use ORM\Database\ORMMeta;

interface BaseModel {
	public function defineORM(): ORMMeta;
}
