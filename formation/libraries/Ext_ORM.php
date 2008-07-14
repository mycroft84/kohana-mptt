<?php
 
class Ext_ORM extends ORM {

	// Database field rules
	protected $validate=array();

	//Acts as behaviour
	protected $acts_as;
	
	public function __construct($id=false)
	{
		parent::__construct($id);
		
		//Handles the acts as behaviour so you can use it
		if(!empty($this->acts_as))
		{
			$class='ORM_'.ucfirst($this->acts_as);
			
			$this->{$this->acts_as}=new $class($this);

		}
	}
	/**
	 * Magic method for calling ORM methods. This handles:
	 *  - as_array
	 *  - find_by_*
	 *  - find_all_by_*
	 *  - find_related_*
	 *  - has_*
	 *  - add_*
	 *  - remove_*
	 *
	 * @throws  Kohana_Exception
	 * @param   string  method name
	 * @param   array   method arguments
	 * @return  mixed
	 */
	public function __call($method, $args)
	{
		if($this->acts_as!=null AND method_exists($method,$this->acts_as))
		{
			return $this->acts_as->$method(explode($args));
		}
		return parent::__call($method,$args);
	}	
	/**
	 * Magic method for setting object and model keys.
	 *
	 * @param   string  key name
	 * @param   mixed   value to set
	 * @return  void
	 */
	public function __set($key, $value)
	{
		//These keys are potential behaviours
		if(in_array($key,array('tree','nestedset','list','versioned')))
		{
			$this->$key=$value;
			return;
		}
		parent::__set($key,$value);
	}
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
				return strtotime($this->object->$key);
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
				//pr($field . '  ' .$value);
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
	public function set_datetime()
	{
		$time=time();
	   if(!$this->exists())
	   {
			//if record doesn't exist it must be created with a time of creation
	       $this->created=gmdate("Y-m-d H:i:s", $time);
	   }
		//Always set a new modifed time
	   $this->modified=gmdate("Y-m-d H:i:s", $time);
	}
	public function get_relationships()
	{
		return array(
			'has_one'=>$this->has_one,
			'has_many'=>$this->has_many,
			'belongs_to'=>$this->belongs_to,
			'belongs_to_many'=>$this->belongs_to_many,
			'has_and_belongs_to_many'=>$this->has_and_belongs_to_many,		
		);
	}
}