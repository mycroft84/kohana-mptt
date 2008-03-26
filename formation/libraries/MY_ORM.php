<?php
 
class ORM extends ORM_Core {

	// Database field rules
	protected $validate=array();


	/**
	 * Saves the current object.
	 *
	 * @return  bool
	 */
	public function save($validate=TRUE)
	{
			
		// No data was changed
		if (empty($this->changed))
			return TRUE;
			
        //Before save
        //Called after changes check to allow changing properties 
        //without saving all the time
        if($this->before_save()==false)
        {
        	return false;
        }

        Event::run(get_class($this).'.before_save');

		$data = array();
		foreach($this->changed as $key)
		{
			// Get changed data
			$data[$key] = $this->object->$key;
		}

		if ($this->object->id == '')
		{
			// Perform an insert
			$query = self::$db->insert($this->table, $data);

			if (count($query) === 1)
			{
				// Set current object id by the insert id
				$this->object->id = $query->insert_id();
			}
		}
		else
		{
			// Perform an update
			$query = self::$db->update($this->table, $data, array('id' => $this->object->id));
		}

		if (count($query) === 1)
		{
			// Reset changed data
			$this->changed = array();

			return TRUE;
		}

		return FALSE;
	}
    private function before_save()
    {
    	return true;
    	foreach (self::$fields[$this->table] as $field => $data)
    	{
    		
    		if($field=='modified' && $data['format']=='0000-00-00 00:00:00')
    		{
    			$this->modified=gmdate("Y-m-d H:i:s", time());
    		}
    		
			if($field=='created' && $data['format']=='0000-00-00 00:00:00' && $this->object->id == '')
    		{
    			$this->created=gmdate("Y-m-d H:i:s", time());
    		}

    	}
    	return true;
    }

  
	/**
	 * Magic method for getting object and model keys.
	 *
	 * @param   string  key name
	 * @return  mixed
	 */
	public function __get($key)
	{
		if($key=='modified' || $key=='created')
		{
			if (isset($this->object->$key))
			{
				
				//return strtotime($this->object->$key);
			}
		}
		
		return parent::__get($key);
	}
  /**
	 * Finds the many<>many relationship table. 
	 *
	 * This override updates the handling of the join table name 
	 * to be alphabetically based for has_and_belongs_to_many relationships.
	 *
	 * @param   string  table name
	 * @return  string
	 */
	protected function related_table($table)
	{
		if (in_array($table, $this->has_and_belongs_to_many))
		{
		  return (strcasecmp($this->table, $table) <= 0) ? $this->table.'_'.$table : $table.'_'.$this->table;
		} else {
		  return parent::related_table($table);
		}
	}
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