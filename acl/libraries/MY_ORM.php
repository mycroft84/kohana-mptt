<?php
class ORM extends ORM_Core{
	//Acts as behaviour
	protected $acts_as;
	
	public function __construct($id=false)
	{
		parent::__construct($id);
		
		//Handles the acts as behaviour so you can use it
		if(!empty($this->acts_as))
		{
			$class='ORM_'.ucfirst($this->acts_as);
			
			$this->acts_as=new $class($this);
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

		if($this->acts_as!=null AND method_exists($this->acts_as,$method))
		{
			return call_user_func_array(array($this->acts_as,$method),$args);
			//return $this->acts_as->$method(implode(',',$args));  
		}
		return parent::__call($method,$args);
	}	
	/**
	 * Loads the database if it is not already loaded. Used during initialization
	 * and unserialization.
	 *
	 * @return  void
	 */
	protected function connect()
	{
		if (self::$db === NULL)
		{
			// Load database, if not already loaded
			(Registry::get('db')==false) and self::$db = Database::instance();
			
			Registry::set('db',self::$db);
		}

		if (empty(self::$fields[$this->table]))
		{
			if ($fields = self::$db->list_fields($this->table))
			{
				foreach ($fields as $field => $data)
				{
					// Cache the column names
					self::$fields[$this->table][$field] = $data;
				}
			}
			else
			{
				// Table doesn't exist
				throw new Kohana_Exception('database.table_not_found', $this->table);
			}
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
}
