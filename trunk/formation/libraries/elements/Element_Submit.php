<?php defined('SYSPATH') or die('No direct script access.');

class Element_Submit_Core extends Element_Input {

	protected $attr = array
	(
		'type'  => 'submit',
		'class'=>'submit'	
		
	);
	public function __construct($name,$value=null)
	{
		
		parent::__construct($name,$value);
	
	}

	public function render()
	{
		$data = $this->attr;
		$data['value']=$this->name;
		$data['name']=$this->name;
				
		return form::button($data);
	}

	public function validate()
	{
		// Submit buttons do not need to be validated
		return $this->is_valid = TRUE;
	}

} // End Form Submit