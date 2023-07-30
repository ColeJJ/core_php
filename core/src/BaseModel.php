<?php

namespace Core;

use Core\Database\ORMMeta;

interface BaseModel {
	public function defineORM(): ORMMeta;
}
