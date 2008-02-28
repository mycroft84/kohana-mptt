<?php defined('SYSPATH') or die('No direct script access.');

class Element_Checkbox_Core extends Element_Input {
	
	protected $attr = array
	(
		'type' => 'checkbox',
		'checked' => FALSE,
	);	

	public function load_value($value)
	{
		if(is_string($value))
		{
			$this->attr['checked'] = true;
		}
		else
		{
			$this->set_value('');
		}
	}

	protected function html_element()
	{

		// Import the data
		$data = $this->attr;
		$data['value']=$this->value;
		$data['name']=$this->name;
		$label = ' '.ltrim($this->label);

		return form::checkbox($data);
	}
	
}
