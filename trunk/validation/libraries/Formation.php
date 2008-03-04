<?php defined('SYSPATH') or die('No direct access allowed.');

class Formation_Core extends Validation{
	
	// Form attributes
	protected $attr = array
	(
		'method'=>'POST'
	);
	
	//Template with the form to load
	protected $template='formation_template';
	
	//Key/value pairs passed onto the template
	protected $template_vars=array();

	/**
	 * Constructor
	 *
	 * @param string $legend
	 */
	public function __construct($legend='Form')
	{
		$this->template_vars['legend']=$legend;
		// Set element autoloader
		spl_autoload_register(array('Validation', 'auto_load'));		

	}
	/**
	 * Set variables for form template
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key,$value)
	{
		$this->template_vars[$key]=$value;
	}
	/**
	 * Get template variables
	 *
	 * @param unknown_type $key
	 * @return unknown
	 */
	public function __get($key)
	{
		if(isset($this->template_vars[$key]))
			return $this->template_vars[$key];
		
		return false;
	}
	/**
	 * Returns the ArrayObject array values.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		$array=array();
		//Only name/value pairs
		foreach($this as $field)
		{
			$array[$field->get_name()]=$field->get_value();			
		}		
		return $array;
	}	
	/**
	 * Validate the form
	 *
	 * @return unknown
	 */
	public function validate()
	{	
		if($this->load_values()!=false)
		{
			//Validate form
			return parent::validate();
		}
		return false;
	}
	/**
	 * Validate partial
	 *
	 * @param unknown_type $partial
	 * @return unknown
	 */
	public function validate_partial($partial)
	{
		if($this->load_values()!=false)
		{
			//Validate form
			return parent::validate_partial($partial);			
		}
		return false;

	}	
	/**
	 * Load values from POST,GET
	 *
	 * @return bool
	 */
	protected function load_values()
	{
		static $method;
		$method=strtolower($this->attr['method']);

		if((Input::instance()->$method())!=array())
		{	
			//Load values from a post
			foreach($this as $name=>$field)
			{
				if(!($field instanceof Element_Group))
				{
					//Load value if present
					$this[$name]->load_value(Input::instance()->$method($name));	
				}
			}
			return true;
		}
		return false;
	}
	/**
	 * Add element to form
	 *
	 * @param Field object, name $type
	 * @param unknown_type $name
	 * @return unknown
	 */
	public function add_element($type,$name=null)
	{
		if($type instanceof Field && $name==null)
		{
			$name=$type->get_name();
			
			$this[$name]=$type;
		}
		else
		{
			if ($name==null)
				throw new Kohana_Exception('formation.invalid_rule', get_class($rule));
				
			$type='Element_'.ucfirst(strtolower($type));
			$this[$name]=new $type($name);	
		}
		return $this[$name];
	}
	/**
	 * Remove element from form
	 *
	 * @param unknown_type $element_name
	 */
	public function remove_element($element_name)
	{
		if(isset($this[$element_name]))
		{
			unset($this[$element_name]);
			return $this;
		}
		return false;
	}
	/**
	 * Returns element object
	 * @return mixed
	 */
	public function get_element($element_name)
	{
		if(isset($this[$element_name]))
		{
			return ($this[$element_name]);
		}
		return false;
	}
	/**
	 * Clear elements from form 
	 * 
	 * @return object
	 */
	public function clear_elements()
	{
		foreach($this as $element_name=>$object)
		{
			if($object instanceof Element_Input)
			{
				$elements[]=$element_name;

			}
		}
		foreach($elements as $element)
		{
			$this->remove_element($element);
		}
		return $this;
	}	
	/**
	 * Add display group to form
	 *
	 * @param array element names to add or element objects
	 * @param name of the group $name
	 */
	public function add_group($group_element=null,$name)
	{
		$this[$name]=new Element_Group($name);
		
		if(is_string($group_element))
		{
			$group_element=array($group_element);
		}
		
		if(is_array($group_element))
		{
			foreach($group_element as $element)
			{
				if(isset($this[$element]))
				{
					$element=$this[$element];
				}
								
				if($element instanceof Element_Input)
				{
					$this[$name]->add_element($element);
					$this->remove_element($element->name);						
				}
				else
				{
					throw new Kohana_Exception('formation.invalid_input', get_class($element));
				}
			}	
		}
	}
	/**
	 * Remove group and its elements from form
	 *
	 * @param string $group_name
	 * @return object
	 */
	public function remove_group($group_name)
	{
		if(isset($this[$group_name]))
		{
			unset($this[$group_name]);
		}
		return $this;
	}
	/**
	 * Returns element object
	 * @return mixed
	 * 
	 */
	public function get_group($group_name)
	{
		if(isset($this[$group_name]))
		{
			return ($this[$group_name]);
		}
		return false;
	}	
	/**
	 * Clears all groups
	 * @return mixed
	 */
	public function clear_groups()
	{
		foreach($this as $group_name=>$object)
		{
			if($object instanceof Element_Group)
			{
				$groups[]=$group_name;

			}
		}
		foreach($groups as $group)
		{
			$this->remove_group($group);
		}
		return $this;
	}
	/**
	 * Set the template for the form
	 *
	 * @param unknown_type $template
	 * @return object
	 */
	public function set_template($template)
	{
		$this->template=$template;
		return $this;
	}
	/**
	 * Get template of the form
	 *
	 * @return string
	 */
	public function get_template()
	{
		return $this->template;
	}
	/**
	 * Render the form with the given template
	 *
	 * @return string
	 */
	public function render($template=null)
	{
		if($template!=null)
		{
			$this->set_template($template);
		}
		
		$form=new View($this->template);
		$form_type = 'open';
			
		// See if we need a multipart form
		foreach ($this as $input)
		{
			if ($input instanceof Element_Upload)
			{
				$form_type = 'open_multipart';
			}
		}
		//Form open and close
		$form->open  = form::$form_type(arr::remove('action', $this->attr), $this->attr);
		$form->close = form::close();

		//Errors and messages passed on to the form, not used in formation_template.php
		$form->errors= $this->errors();
		
		// Set the inputs
		$form->inputs = $this;
		
		//Set any template vars set using __set()
		$form->set($this->template_vars);
		
		return $form;	
	}
	/**
	 * Returns the form HTML
	 */
	public function __toString()
	{
		return (string) $this->render();
	}
	/**
	 * Set a form attribute. This method is chainable.
	 *
	 * @param   string        attribute name, or an array of attributes
	 * @param   string        attribute value
	 * @return  object
	 */
	public function set_attr($key, $val = NULL)
	{
		// Set the new attribute
		$this->attr[$key] = $val;
		return $this;
	}
	/**
	 * Return attribute of <form>
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get_attr($key)
	{
		if(isset($this->attr[$key]))
		{
			return $this->attr[$key];
		}
		return false;
	}
	/**
	 * Set the method for the form
	 *
	 * @param string $method
	 * @return object
	 */
	public function set_method($method)
	{
		return $this->set_attr('method',$method);
	}
	/**
	 * Get the method of the form
	 *
	 * @return mixed
	 */
	public function get_method()
	{
		return $this->get_attr('method');
	}
	/**
	 * Set action of the form
	 *
	 * @param string $action
	 * @return object
	 */
	public function set_action($action)
	{
		return $this->set_attr('action',$action);
	}
	/**
	 * Get action of the form
	 *
	 * @return string
	 */
	public function get_action()
	{
		return $this->get_attr('action');
	}	
	/**
	 * set values of the form e.g. form db
	 *
	 * @param array $data
	 * @return object
	 */
	public function set_values(array $data)
	{
		foreach($data as $key=>$value)
		{			
			if(isset($this[$key]))
			{			
				$this[$key]->set_value($value);
			}
		}
		return $this;
	}	
}
?>