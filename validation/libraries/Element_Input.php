<?php

class Element_Input_Core extends Field {
	
	protected $attr=array
	(
		'type'    => 'text',
	);
	
	protected $label;

	// Message output format
	protected $message_format = '{message}';
	

	/**
	 * Magically gets a variable.
	 *
	 * @param   string  variable key
	 * @return  mixed   variable value if the key is found
	 * @return  void    if the key is not found
	 */
	public function __get($key)
	{	
		//name and value are attributes but not accesible as such
		if($key=='name'||$key=='value')
		{
			return parent::__get($key);
		}
		return $this->get_attr($key);
		
	}
	/**
	 * Magically sets a variable.
	 * @param   string  variable key
	 * @param   string  variable value
	 * @return  object  object
	 * @return  void    if the key is not found
	 */	
	public function __set($key,$value)
	{
		if($key=='name'||$key=='value')
		{
			return parent::__set($key,$value);
		}
		return $this->set_attr($key,$value);
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
		if($key=='name'||$key=='value')
			return false;
			
		// Set the new attribute
		$this->attr[$key] = $val;
		
		return $this;
	}
	/**
	 * Get a form attribute
	 *
	 * @param unknown_type $key
	 * @return unknown
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
	 * Returns instance of a label
	 *
	 * @return unknown
	 */
	public function label()
	{
		if(!($this->label instanceof Element_Label))
		{
			$this->label=new Element_Label($this->name);
		}
		return $this->label;
	}
	/**
	 * Renders input
	 *
	 * @return unknown
	 */
	public function render()
	{
		// Make sure validation runs
		$this->validate();

		return $this->html_element();
	}
	/**
	 * Loads value, 
	 *
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function load_value($value)
	{
		
		$this->set_value($value);
		return $this;
	}
	/**
	 * Returns the form input HTML.
	 *
	 * @return  string
	 */
	protected function html_element()
	{
		$data = $this->attr;
		$data['value']=$this->value;
		$data['name']=$this->name;
		
		return form::input($data);
	}
}
?>