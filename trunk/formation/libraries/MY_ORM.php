<?php
 
class ORM extends ORM_Core {

	// Database field rules
	protected $validate=array();

	/**
	 * Load array of values into ORM object
	 *
	 * @param array $data
	 */
	public function load_values(array $data)
	{
		foreach ($data as $field=>$value)
		{
			if(array_key_exists($field,self::$fields[$this->table]))
			{
				$this->$field=$value;
			}
		}

	}	
	/** 
	 * Retrieve field_data from ORM
	 */
	public function list_fields()
	{
		return self::$fields[$this->table];	
	}

	/**
	 * Get validation rules from ORM
	 */
	public function get_validate()
	{
		return $this->validate;
	}
	/**
	 * Simple exists method to see if model exists
	 *
	 * @return boolean
	 */
	public function exists()
	{
		return $this->object->id > 0;
	}
}