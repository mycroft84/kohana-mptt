<?php

class Forge extends Forge_Core {
	public function error_message($func,$args)
	{

		// Force args to be an array
		$args = is_array($args) ? $args : array();

		// Add the label or name to the beginning of the args
		array_unshift($args, $this->label ? strtolower($this->label) : $this->name);

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
	public function get_attr($attr)
	{
		if(array_key_exists($attr,$this->attr))
			return $this->attr[$attr];
				
		return false;
	}
}
