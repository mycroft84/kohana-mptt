<?php defined('SYSPATH') or die('No direct script access.');

class Element_Radio_Core extends Element_Input {
	protected $options=array();
	
	public function set_options($options)
	{
		$this->options=$options;
		return $this;		
	}
	public function get_options($options)
	{
		$this->options=$options;
	}	
	public function render()
	{
		// Import base data
		$base_data = array();//$this->data;
		$base_data['name']=$this->name;


		// Newline
		$nl = "\n";
		
		$radiolist = $nl.'<ul class="'.arr::remove('class', $this->attr).'">'.$nl;		
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
			$radiolist .= "\t".'<li><label>'.form::radio($data).' '.$title.'</label></li>'.$nl;
		}
		$radiolist .= '</ul>';

		return $radiolist;
	}
	
}
