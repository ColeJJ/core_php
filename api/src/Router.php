<?php

namespace API;

class Router {
	public function findClass(string $obj, string $requestedEndpoint) {
		$endpoints = include "./". $obj . "/EndpointMap.php";
		
		foreach ($endpoints as $ep => $class) {
			if ($ep === $requestedEndpoint) {
				return $class;
			}
		}
	}
}
?>
