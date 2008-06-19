<?php 
class Database_Expr { 
	private $_value = NULL; 
	public function __construct($value) 
	{ 
	$this->_value = $value; 
	} 
	public function __toString() 
	{ 
	return $this->_value; 
	} 

} 