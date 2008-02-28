<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Validation library.
 *
 * $Id: Validation.php 2070 2008-02-17 05:56:26Z armen $
 *
 * @package    Validation
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Validation_Core extends ArrayObject {

	// Errors
	protected $errors = array();
	protected $messages = array();

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
	 * Sets the unique "any field" key and creates an ArrayObject from the
	 * passed array.
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
			$array_object[$key]=new Field($key,$value,$this); 
		}
		parent::__construct($array_object, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
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
	 * Get all post filters
	 *
	 * @return object
	 */
	public function get_rules()
	{
		foreach($this as $field)
		{
			$callbacks[$field->name]=$field->get_rules();
		}
		return $this;
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
		foreach($this as $field)
		{
			$callbacks[$field->name]=$field->get_pre_filters();
		}
		return $this;
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
		foreach($this as $field)
		{
			$callbacks[$field->name]=$field->get_post_filters();
		}
		return $this;
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
		foreach($this as $field)
		{
			$callbacks[$field->name]=$field->get_callbacks();
		}
		return $this;
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
				$this->errors=array_merge($field->errors(),$this->errors);
				$this->messages=array_merge($field->error_messages(),$this->messages);
				
			}
			else
			{
				if($is_valid==false)
				{
					$this->errors[$key]=$field->error();
					$this->messages[$key]=$field->error_message();	
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
						$this->errors=array_merge($field->errors(),$this->errors);
						$this->messages=array_merge($field->error_messages(),$this->messages);
					}
					else
					{	
						if($this[$field]->error()!= null)
						{
							$this->errors[$this[$field]->name]=$this[$field]->error();
							
							$this->messages[$this[$field]->name]=$this[$field]->error_message();	
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
	 * @param unknown_type $array
	 * @return unknown
	 */
	public function validate_partial_json($array)
	{
		return jsonencode($this->validate_partial($array));
	}
	/**
	 * Return the errors array.
	 *
	 * @return array
	 */
	public function errors()
	{
		return $this->errors;
	}
	/**
	 * Return error messages
	 *
	 * @return array
	 */
	public function error_messages()
	{
		return $this->messages;
	}
	/**
	 * Provides a generic interface to load the errors.
	 */
	public function load_errors($form_name = '')
	{
		foreach ($this->errors as $input => $error)
		{
			$key = ($form_name)?
				"forms.$form_name.$input.$error" :
				"forms.$input.$error";

			if (($str = Kohana::lang($key)) === $key)
			{
				// Get the non-error-specific message
				$key = ($form_name)?
					"forms.$form_name.$input.default" :
					"forms.$input.default";
			
				$str = Kohana::lang($key);
			}

			// Add the message
			$this->message($input, $str);
		}
	}	

} // End Validation
