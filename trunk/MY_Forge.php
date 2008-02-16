<?php

class Forge extends Forge_Core {
	//Fetches error message for the function see i18n/*/validation.php for names
	//$args can be array or string, first value will be the name of the field
	public function error_message($func,$args)
	{
		
		// Force args to be an array
		if(!is_array($args))
		{
			$args=array($args);
		}

		if (isset($this->error_messages[$func]))
		{
			// Use custom error message
			$error = vsprintf($this->error_messages[$func], $args);
		}
		else
		{
			// Fetch an i18n error message
			$error = Kohana::lang('validation.'.$func, $args);
			
		}

		return $error;
	}
	//gets set attributes
	public function get_attr($attr)
	{
		if(array_key_exists($attr,$this->attr))
			return $this->attr[$attr];
				
		return false;
	}
}
