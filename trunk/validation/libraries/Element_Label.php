<?php

class Element_Label_Core extends Element_Input {
	
	protected $attr=array();
	protected $text;
	
	public function __construct($name,$text=null)
	{
		$this->set_attr('for',$name);
		if($text==null)
		{
			$text=utf8::ucwords(inflector::humanize($name)).' ';
		}
		$this->set_text($text);
	}
	public function set_text($text)
	{
		$this->text=$text;
	}
	public function get_text()
	{
		return $this->text;
	}
	public function render()
	{
		return form::label($this->attr,$this->text);
		
	}		
	/**
	 * Returns the form HTML
	 */
	public function __toString()
	{
		return $this->render();
	}	
}