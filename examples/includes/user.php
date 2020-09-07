<?php

class User extends PicoORM {
	
	public function __construct($id_value, $id_column = 'id') {
		// do something special
		parent::__construct($id_value, $id_column);
	}
	
	public function customFunction($value, $data) {
		// do something with $value and $data
	}
}