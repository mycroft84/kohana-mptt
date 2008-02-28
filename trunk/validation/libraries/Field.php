<?php

class Field_Core {

	//Field name
	protected $name;
	
	//Friendly name for in error messages
	protected $screen_name;

	//Field value
	protected $value;	
	
	//The first error for a field ends up here
	protected $errors;
	//The arguments for the rule that triggered the error 
	protected $args;
		
	// Filters
	protected $pre_filters 	= array();
	protected $post_filters = array();

	// Rules and callbacks
	protected $rules 		= array();
	protected $callbacks 	= array();
	
	// Message output format
	protected $message_format = '<p class="error">{message}</p>';
	
	//bool whether field is validated
	protected $is_valid;

	//Unfiltered value, also no rules or callbacks are applied to this
	protected $unfiltered_value;

	//Custom error messages, if none defaults to Kohana i18n validation.php
	protected $error_messages=array();
	
	//Validation object, useful for callbacks
	protected $validation;
	
	/**
	 * Construct 
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @param Validate $validation_object
	 */
	public function __construct($name,$value=null,Validation $validation_object=null)
	{
		
		$this->name=$name;
		$this->value=$value;
		$this->unfiltered_value=$value;
		
		//Pass validation object for callbacks
		if($validation_object!=null)
		{
			$this->validation=$validation_object;
		}
	}
	/**
	 * Convert object to string when echoed
	 *
	 * @return value
	 */
	public function __toString()
	{
		return $this->value;
	}
	/**
	 * Magically gets vars
	 *
	 * @param string $key
	 * @return string|bool
	 */
	public function __get($key)
	{
		if(isset($this->$key))
		{
			return $this->$key;
		}
		return false;
	}
	/**
	 * Magically sets vars
	 *
	 * @param string  $key
	 * @param string $value
	 */
	public function __set($key,$value)
	{
		//Cannot change name
		if($key=='name')
			return false;
			
		if(isset($this->$key)&&$this->$key!=$value)
		{
			$this->$key=$value;
		}
		
	}
	/**
	 * Validate the object
	 *
	 * @return boolean
	 */
	public function validate()
	{
		if(is_bool($this->is_valid))
			return $this->is_valid;
		
		//Iterate over filters, rules and callbacks
		foreach ($this->pre_filters as $filter)
		{
			//Filter value, or every value of an array
			$this->value = is_array($this->value) ? array_map($filter, $this->value) : call_user_func($filter, $this->value);
		}	
		foreach ($this->rules as $rule)
		{
			
			
			// Split the rule into function and args
			list($func, $args) = $rule;	
				
			// Prevent other rules from running when this field already has errors
			if ( ! empty($this->errors)) break;
			
			// Don't process rules on empty fields
			if (($func[1] !== 'required' AND $func[1] !== 'matches') AND empty($this->value))
				continue;
			
			// Run each rule, pass value and then arguments
			if ( ! call_user_func($func, $this->value, $args))
			{	
				$error=is_array($func) ? $func[1] : $func;

				$this->add_error($error,$args);
				// Stop after an error is found
				break;
			}				
		}		
		foreach ($this->callbacks as $callback)
		{
			// Execute the callback, $this is passed so you can access entire validation procedure
			// Mind that when the validation is done some rules might be carried out, some not 
			call_user_func($callback, $this);

			// Stop after an error is found
			if ( ! empty($this->errors)) break;
		}		
		foreach ($this->post_filters as $filter)
		{
			//Filter value, or every value of an array
			$this->value = is_array($this->value) ? array_map($filter, $this->value) : call_user_func($filter, $this->value);
		}	
		
		$this->is_valid=(count($this->errors) === 0);

		// Return TRUE if there are no errors
		return $this->is_valid;
	}
	/**
	 * Get unfiltered value
	 * return mixed
	 */
	public function get_unfiltered_value()
	{
		return $this->unfiltered_value;
	}
	/**
	 * Get value of field
	 *
	 * @return unknown
	 */
	public function get_value()
	{
		return $this->value;
	}
	/**
	 * Set value of field
	 *
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function set_value($value)
	{
		$this->value=$value;
		return $this;
	}	
	/**
	 * Get name of field
	 *
	 * @return unknown
	 */
	public function get_name()
	{
		return $this->name;
	}	
	/**
	 * Set screen name
	 */
	public function set_screen_name($name)
	{
		$this->screen_name=$name;
		return $this;
	}
	public function get_screen_name($name)
	{
		return $this->screen_name;
	}
	/**
	 * Return pre filters
	 *
	 * @return array
	 */
	public function get_pre_filters()
	{
		return $this->pre_filters;
	}	
	/**
	 * Add a pre-filter to the object
	 *
	 * @chainable
	 * @param   callback  filter
	 * @return  object
	 */
	public function add_pre_filter($filter)
	{
		if ( ! is_callable($filter))
			throw new Kohana_Exception('validation.filter_not_callable');

		$filter = (is_string($filter) AND strpos($filter, '::') !== FALSE) ? explode('::', $filter) : $filter;			

		// Add the filter to specified field
		$this->pre_filters[] = $filter;

		return $this;
	}
	/**
	 * Add a pre-filter to the object
	 *
	 * @param array callback filters
	 * @return object
	 */
	public function add_pre_filters($filters)
	{
		foreach($filters as $filter)
		{
			$this->add_pre_filter($filter);
		}
		return $this;
	}	
	/**
	 * Remove pre filter
	 *
	 * @param callback $filter
	 * @return object
	 */
	public function remove_pre_filter($filter)
	{
		$filter=array_search($filter,$this->pre_filters);
		if($filter !== FALSE)
		{
			unset($this->pre_filters[$filter]);
			return $this;
		}
		
		return false;
	}
	/**
	 * Clear all pre filters
	 *
	 * @return object
	 */
	public function clear_pre_filters()
	{
		$this->pre_filters=array();
		return $this;
	}

	/**
	 * Return post filters
	 *
	 * @return array
	 */
	public function get_rules()
	{
		return $this->rules;
	}
	/**
	 * Add rule to the object
	 *
	 * @chainable
	 * @param   callback  rule
	 * @return  object
	 */
	public function add_rule($rule)
	{
		$rule=$this->parse_rule($rule);
		//name string rules
		if($rule[2]!=null)
		{
			$name=array_pop($rule);
			$this->rules[$name]= $rule;
		}
		else
		{
			$this->rules[]=$rule;
		}
		

		return $this;
	}	
	

	/**
	 * Add array of rules
	 *
	 * @param array $rules
	 * @return object
	 */
	public function add_rules(array $rules)
	{
		foreach($rules as $rule)
		{
			$this->add_rule($rule);
		}
		return $this;
	}
	/**
	 * Remove rule
	 * Problem when removing callbacks with $this
	 *
	 * @param unknown_type $rule_name
	 * @return unknown
	 */
	public function remove_rule($rule)
	{

		if(isset($this->rules[$rule]))
		{
			unset($this->rules[$rule]);
			return $this;
		}
		else
		{
			$rule=array_search($rule,$this->rules);
			if($rule !== FALSE)
			{
				unset($this->rules[$rule]);
				return $this;
			}
		}
		
		return false;
	}
	/**
	 * Clear all rules
	 *
	 * @return unknown
	 */
	public function clear_rules()
	{
		$this->rules=array();
		return $this;
	}
	/**
	 * Return post filters
	 *
	 * @return array
	 */
	public function get_post_filters()
	{
		return $this->post_filters;
	}	
	/**
	 * Add a pre-filter to the object
	 *
	 * @chainable
	 * @param   callback  filter
	 * @return  object
	 */
	public function add_post_filter($filter)
	{
		if ( ! is_callable($filter))
			throw new Kohana_Exception('validation.filter_not_callable');

		$filter = (is_string($filter) AND strpos($filter, '::') !== FALSE) ? explode('::', $filter) : $filter;			

		// Add the filter to specified field
		$this->post_filters[] = $filter;

		return $this;
	}	
	/**
	 * Add a post-filter to the object
	 *
	 * @param array callback filters
	 * @return object
	 */
	public function add_post_filters($filters)
	{
		foreach($filters as $filter)
		{
			$this->add_post_filter($filter);
		}
		return $this;
	}
	/**
	 * Remove post filter
	 *
	 * @param callback $filter
	 * @return object
	 */
	public function remove_post_filter($filter)
	{
		$filter=array_search($filter,$this->pre_filters);
		if($filter !== FALSE)
		{
			unset($this->post_filters[$filter]);
			return $this;
		}
		
		return false;
	}
	/**
	 * Clear all post filters
	 *
	 * @return object
	 */
	public function clear_post_filters()
	{
		$this->post_filters=array();
		return $this;
	}	
	/**
	 * Return callbacks
	 *
	 * @return array
	 */
	public function get_callbacks()
	{
		return $this->callbacks;
	}
	/**
	 * Add a callback to the object
	 *
	 * @param callback callback
	 * @return object
	 */
	public function add_callback($callback)
	{

		if ( ! is_callable($callback, TRUE))
			throw new Kohana_Exception('validation.callback_not_callable');

		$callback = (is_string($callback) AND strpos($callback, '::') !== FALSE) ? explode('::', $callback) : $callback;							

		// Add the filter to specified field
		$this->callbacks[] = $callback;

		return $this;
	}	
	/**
	 * Add array of callbacks to the object
	 *
	 * @param array callbacks
	 * @return object
	 */
	public function add_callbacks(array $callbacks)
	{
		foreach($callbacks as $callback)
		{
			$this->add_callback($callback);	
		}
		return $this;
	}	
	/**
	 * Remove post filter
	 *
	 * @param callback $filter
	 * @return object
	 */
	public function remove_callback($callback)
	{
		$callback=array_search($callback,$this->callbacks);
		if($callback !== FALSE)
		{
			unset($this->callbacks[$callback]);
			return $this;
		}
		
		return false;
	}
	/**
	 * Clear all post filters
	 *
	 * @return object
	 */
	public function clear_callbacks()
	{
		$this->callbacks=array();
		return $this;
	}		

	/**
	 * Return the errors array.
	 *
	 * @return array
	 */
	public function error()
	{
		return $this->errors;
	}	
	/**
	 * Add an error to an input.
	 *
	 * @chainable
	 * @param   string  error
	 * @param   array  error arguments
	 * @return  object
	 */
	public function add_error($error,$args=null)
	{
		$this->errors = $error;
		$this->args   = $args;
		
		return $this;
	}	
	/**
	 * Remove error from current field
	 * Might be used by callbacks 
	 * @return object
	 */
	public function remove_error()
	{
		$this->errors = null;
		$this->args   = null;
		
		return $this;
	}
	/**
	 * Set error message
	 *
	 * @param unknown_type $error
	 * @param unknown_type $args
	 */
	public function set_error_message($error,$value)
	{
		$this->error_messages[$error]=$value;
		return $this;
	}
	/**
	 * Set the format of message strings for particular fields
	 *
	 * @chainable
	 * @param   string   new message format
	 * @return  object
	 */
	public function error_format($str)
	{
		if (strpos($str, '{message}') === FALSE)
			throw new Kohana_Exception('validation.error_format');

		// Set the new message format
		$this->message_format = $str;

		return $this;
	}	
	/**
	 * Returns error	 * format
	 *
	 * @return string
	 */
	public function get_error_format()
	{
		return $this->message_format;
	}

	/**
	 * Returns the message for an input. 
	 *
	 * @return  string
	 */
	public function error_message()
	{
		//No errors, no messages
		if (empty($this->errors))
			return false;
		
		//If arguments present 
		$replace=array();
		
		if(!empty($this->args))
		{
			$replace=$this->args;
		}
		
		//Set a friendly field name just the standard name
		$name=(empty($this->screen_name)) ? $this->name : $this->screen_name; 

		//Place the name in front of the array
		array_unshift($replace,$name);
		
		//Check for custom messages
		if(array_key_exists($this->error(),$this->error_messages))
		{
			$message=$this->error_messages[$this->error()];
		}
		else
		{//resort to default messages
			$error=($this->error()=='email') ? 'valid_email' : $this->error();
			//Get error string
			$message=Kohana::lang('validation.'.$error);	
		}

		//Replace stuff in the error string with vars
		$message = (strpos($message, '%s') !== FALSE) ? vsprintf($message, $replace) : $message;

		// Return the HTML message string
		return str_replace('{message}', $message, $this->message_format);

	}	
	public function set_required($required=true)
	{
		if($required==true)
		{
			return $this->add_rule('required');
		}
		return $this->remove_rule('required');
	}


	/**
	 * Rule: required. Generates an error if the field has an empty value.
	 *
	 * @param   mixed   input value
	 * @return  bool
	 */
	public function required($str)
	{
		return ! ($str === '' OR $str === NULL OR $str === FALSE OR (is_array($str) AND empty($str)));
	}
	
	/**
	 * Rule: matches. Generates an error if the field does not match one or more
	 * other fields.
	 *
	 * @param   mixed   input value
	 * @param   array   input names to match against
	 * @return  bool
	 */
	//TODO fix it
	public function matches($str, array $inputs)
	{
		foreach ($inputs as $key)
		{
			if ($str !== (isset($key) ? $key : NULL))
				return FALSE;
		}

		return TRUE;
	}
	/**
	 * Rule: length. Generates an error if the field is too long or too short.
	 *
	 * @param   mixed   input value
	 * @param   array   minimum, maximum, or exact length to match
	 * @return  bool
	 */
	public function length($str, array $length)
	{
		if ( ! is_string($str))
			return FALSE;

		$size = utf8::strlen($str);
		$status = FALSE;

		if (count($length) > 1)
		{
			list ($min, $max) = $length;

			if ($size >= $min AND $size <= $max)
			{
				$status = TRUE;
			}
		}
		else
		{
			$status = ($size === (int) $length[0]);
		}

		return $status;
	}
	/**
	 * Rule: length. Generates an error if the field is too long or too short.
	 *
	 * @param   mixed   input value
	 * @param   array   minimum, maximum, or exact length to match
	 * @return  bool
	 */
	public function min_length($str, array $length)
	{
		if ( ! is_string($str))
			return FALSE;

		$size = utf8::strlen($str);
		$status = FALSE;

		if (count($length) > 1)
		{
			list ($min, $max) = $length;

			if ($size >= $min AND $size <= $max)
			{
				$status = TRUE;
			}
		}
		else
		{
			$status = ($size === (int) $length[0]);
		}

		return $status;
	}		
	/**
	 * Parse a rule, get its arguments etc.
	 * internal function
	 */
	protected function parse_rule($rule)
	{
		//Rule arguments
		$args = NULL;
		
		$rule_name=null;
		
		if (is_string($rule))
		{
			//Set a rulename if it's a string, necessary for removing
			$rule_name=$rule;
			
			if (preg_match('/^([^\[]++)\[(.+)\]$/', $rule, $matches))
			{
				// Split the rule into the function and args
				$rule = $matches[1];
				$args = preg_split('/(?<!\\\\),\s*/', $matches[2]);

				// Replace escaped comma with comma
				$args = str_replace('\,', ',', $args);
			}
			
			if (method_exists($this, $rule))
			{
				// Make the rule a valid callback
				$rule = array($this, $rule);
			}
			
		}

		if ( ! is_callable($rule, TRUE))
			throw new Kohana_Exception('validation.rule_not_callable');

		$rule = (is_string($rule) AND strpos($rule, '::') !== FALSE) ? explode('::', $rule) : $rule;
		
		return array($rule, $args,$rule_name);
	}	
}
?>