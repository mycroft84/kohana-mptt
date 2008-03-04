<?php defined('SYSPATH') or die('No direct script access.');
class Validation_Core extends ArrayObject {

	// Errors
	protected $errors = array();

	/**
	 * Creates a new Validation instance.
	 *
	 * @param   array   array to use for validation
	 * @return  object
	 */
	public static function factory($array = NULL)
	{
		return new Validation( ! is_array($array) ? $_POST : $array);
	}
	/**
	 * Pass the array you need to validate, e.g. $_POST
	 * 
	 *
	 * @param   array   array to validate
	 * @return  void
	 */
	public function __construct(array $array)
	{
		//For each field make an object
		$array_object=array();
		
		foreach($array as $key=>$value)
		{
			//Create object for each key in array
			$array_object[$key]=new Field($key,$value,$this); 
		}
		//Register auto load for rules and elements
		spl_autoload_register(array('Validation', 'auto_load'));
		
		parent::__construct($array_object, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
			
	}
	/**
	 * Autoloader so elements can be stuffed in an subdir
	 *
	 * @param class $class
	 * @return bool
	 */
	public static function auto_load($class)
	{
		if((substr($class, 0, 7))!='Element' && substr($class, 0, 4)!='Rule' )
			return FALSE;
			
		static $prefix;
		
		// Set the extension prefix
		empty($prefix) and $prefix = Config::item('core.extension_prefix');

		if (class_exists($class, FALSE))
			return TRUE;
		
		$file=strpos($class,'Core') ? substr($class, 0, -5) : $class;
	
		$type = (substr($class, 0, 7)=='Element') ? 	'libraries/elements' : 'libraries/rules';		
	
		// If the file doesn't exist, just return
		if (($filepath = Kohana::find_file($type, $file)) === FALSE)
			return FALSE;
		
		// Load the requested file
		require_once $filepath;
		
		if ($extension = Kohana::find_file($type, $prefix.$class))
		{
			// Load the class extension
			require_once $extension;
		}
		elseif (substr($class, -5) !== '_Core' AND class_exists($class.'_Core', FALSE))
		{
			// Transparent class extensions are handled using eval. This is
			// a disgusting hack, but it works very well.
			eval('class '.$class.' extends '.$class.'_Core { }');
		}
		

		return class_exists($class, FALSE);			
	}	
	/**
	 * Returns the ArrayObject array values.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		//Only name/value pairs
		foreach($this as $field)
		{
			$array[$field->get_name()]=$field->get_value();
		}		
		return $array;
	}
	/**
	 * Get all rules
	 *
	 * @return object
	 */
	public function get_rules()
	{
		$rules=array();
		foreach($this as $field)
		{
			$rules[$field->name]=$field->get_rules();
		}
		return $rules;
	}	
	/**
	 * Add rule to all fields
	 * 
	 * @chainable
	 * @param callback rule
	 * @return object
	 */
	public function add_rule($rule)
	{
		//Add rule to every field
		foreach($this as $field)
		{
			$field->add_rule($rule);
		}
		return $this;
	}
	/**
	 * Add rules to all fields
	 *
	 * @param array $rules
	 * @return object
	 */
	public function add_rules(array $rules)
	{
		foreach($this as $field)
		{
			$field->add_rules($rules);
		}
		return $this;
	}
	/**
	 * Clear all rules
	 *
	 * @return object
	 */
	public function clear_rules()
	{
		foreach($this as $field)
		{
			$field->clear_rules();
		}
		return $this;
	}	
	/**
	 * Get all pre filters
	 *
	 * @return object
	 */
	public function get_pre_filters()
	{
		$pre_filters=array();
		foreach($this as $field)
		{
			$pre_filters[$field->name]=$field->get_pre_filters();
		}
		return $pre_filters;
	}
	/**
	 * Add pre filter to all fields
	 * 
	 * @chainable	 
	 * @param  callback filter
	 * @return object
	 */
	public function add_pre_filter($filter)
	{
		//Add pre filter to every field
		foreach($this as $field)
		{
			$field->add_pre_filter($filter);
		}
		return $this;
	}
	/**
	 * Add pre filters to all fields
	 *
	 * @param array $filters
	 * @return object
	 */
	public function add_pre_filters(array $filters)
	{
		foreach($this as $field)
		{
			$field->add_pre_filters($filters);
		}
		return $this;
	}	
	/**
	 * Clear all pre filters
	 *
	 * @return object
	 */
	public function clear_pre_filters()
	{
		foreach($this as $field)
		{
			$field->clear_pre_filters();
		}
		return $this;
	}	
	/**
	 * Get all post filters
	 *
	 * @return object
	 */
	public function get_post_filters()
	{
		$post_filters=array();
		foreach($this as $field)
		{
			$post_filters[$field->name]=$field->get_post_filters();
		}
		return $post_filters;
	}	
	/**
	 * Add post filter to all fields
	 * 
	 * @chainable	 
	 * @param  callback filter
	 * @return object
	 */
	public function add_post_filter($filter)
	{
		//Add post filter to every field
		foreach($this as $field)
		{
			$field->add_post_filter($filter);
		}
		return $this;
	}
	/**
	 * Add post filters to all fields
	 *
	 * @param array $filters
	 * @return object
	 */
	public function add_post_filters(array $filters)
	{
		foreach($this as $field)
		{
			$field->add_post_filters($filters);
		}
		return $this;
	}	
	/**
	 * Clear all post filters
	 *
	 * @return object
	 */
	public function clear_post_filters()
	{
		foreach($this as $field)
		{
			$field->clear_post_filters();
		}
		return $this;
	}
	/**
	 * Get all callbacks
	 *
	 * @return object
	 */
	public function get_callbacks()
	{
		$callbacks=array();
		foreach($this as $field)
		{
			$callbacks[$field->name]=$field->get_callbacks();
		}
		return $callbacks;
	}
	/**
	 * Add callback to all fields
	 * 
	 * @chainable	 
	 * @param  callback callback
	 * @return object
	 */	
	public function add_callback($callback)
	{
		//Add callback to every field
		foreach($this as $field)
		{
			$field->add_callback($callback);
		}
		return $this;
	}
	/**
	 * Add callbacks to all fields
	 * 
	 * @chainable	 
	 * @param  callback callback
	 * @return object
	 */	
	public function add_callbacks(array $callbacks)
	{
		//Add callback to every field
		foreach($this as $field)
		{
			$field->add_callbacks($callbacks);
		}
		return $this;
	}	
	/**
	 * Clear all callbacks
	 *
	 * @return object
	 */
	public function clear_callbacks()
	{
		foreach($this as $field)
		{
			$field->clear_callbacks();
		}
		return $this;
	}
	/**
	 * Set the format of message strings.
	 *
	 * @chainable
	 * @param   string   new message format
	 * @return  object
	 */
	public function error_format($str)
	{
		if (strpos($str, '{message}') === FALSE)
			throw new Kohana_Exception('validation.error_format');

		foreach($this as $field)
		{
			$field->error_format($str);
		}
		return $this;
	}
	/**
	 * Validate by processing pre-filters, rules, callbacks, and post-filters.
	 * All fields that have filters, rules, or callbacks will be initialized if
	 * they are undefined. Validation will only be run if there is data already
	 * in the array.
	 *
	 * @return bool
	 */
	public function validate()
	{	
		//Iterate over all fields and collect errors and error messages
		foreach($this as $key=>$field)
		{
			//Will validate the Element_Group and load its values as well
			$is_valid=$field->validate();

			if($field instanceof Element_Group)
			{
				$this->errors=array_merge($field->error(),$this->errors);
			}
			else
			{
				if($is_valid==false)
				{
					$this->errors[$key]=$field->error();
				}
			}
		}		
		// Return TRUE if there are no errors
		return (count($this->errors) === 0);
	}
	public function validate_partial($partial)
	{
		if(is_string($partial))
		{
			$partial=array($partial);
		}
		if(is_array($partial))
		{
			foreach($partial as $field)
			{
		
				if(isset($this[$field]))
				{	
					//Will validate the Element_Group and load its values as well
					$this[$field]->validate();
					
					if($this[$field] instanceof Element_Group)
					{
						$this->errors=array_merge($field->error(),$this->errors);
					}
					else
					{	
						if($this[$field]->error()!= null)
						{
							$this->errors[$this[$field]->name]=$this[$field]->error();
						}
					}	
				}
			}
		}
		return (count($this->errors) === 0);
	}
	/**
	 * Validate partial json
	 *
	 * @param array fields you want to validate
	 * @return json boolean
	 */
	public function validate_partial_json($array)
	{
		return jsonencode($this->validate_partial($array));
	}
	/**
	 * Return the errors and messages array.
	 *
	 * @return array
	 */
	public function errors()
	{
		return $this->errors;
	}
	public function set_language_file($file)
	{
		foreach($this as $field)
		{
			$field->set_language_file($file);
		}
	}
} // End Validation
