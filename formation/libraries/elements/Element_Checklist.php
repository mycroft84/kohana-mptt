<?php defined('SYSPATH') or die('No direct script access.');

class Element_Checklist_Core extends Element_Input {

	protected $options=array();
	
	protected $attr=array(
	'class'=>'checklist',
	);
	public function __get($key)
	{
		if($key=='value')
		{
			return $this->get_value();
		}
		return parent::__get($key);
	}
	public function __set($key,$value)
	{
		if($key=='value')
		{
			return $this->set_value($value);
		}
		return parent::__set($key,$value);
	}
	public function set_options($options)
	{
		$this->options=$options;
		return $this;		
	}
	public function get_options($options)
	{
		$this->options;
	}
	public function render()
	{
		// Import base data
		$base_data = array();//$this->data;
		$base_data['name']=$this->name;
		// Make it an array
		$base_data['name'] .= '[]';

		// Newline
		$nl = "\n";
		
		$checklist = $nl.'<ul class="'.arr::remove('class', $this->attr).'">'.$nl;
		foreach($this->options as $val => $opt)
		{
			// New set of input data
			$data = $base_data;

			// Get the title and checked status
			list ($title, $checked) = $opt;

			// Set the name, value, and checked status
			$data['value']   = $val;
			$data['checked'] = $checked;
			//TODO Element_Checkboxes
			$checklist .= "\t".'<li><label>'.form::checkbox($data).' '.$title.'</label></li>'.$nl;
		}
		$checklist .= '</ul>';

		return $checklist;
	}
	public function get_value()
	{
		// Return the currently checked values
		$array = array();
		foreach($this->options as $id => $opt)
		{
			// Return the options that are checked
			($opt[1] === TRUE) and $array[] = $id;
		}
		return $array;
	}
	public function set_value($value)
	{
		foreach($this->options as $val => $checked)
		{
			if ($value != false)
			{
				$this->options[$val][1] = in_array($val, $value);
			}
			else
			{
				$this->options[$val][1] = FALSE;
			}
		}
	}
}