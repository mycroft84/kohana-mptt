<?php

class Element_Hidden_Core extends Element_Input {
	
	protected $attr = array
	(

	);
	public function render()
	{
		$data = $this->data;
		$data[$this->name]=$this->value;
		
		return form::hidden($data);
	}	
	public function label()
	{
		return '';
	}
}
?>