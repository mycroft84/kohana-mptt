<?php

class Forge extends Forge_Core {
	
	public function __construct($action = '', $title = '', $method = NULL, $class = 'form')
	{
		parent::__construct($action,$title,$method,$class);
		
	}	
	
	public function jquery_validation_object()
	{
		$jquery= array();
		foreach ($this->inputs as $field=>$input)
		{
			if($input->name!='')
			{
				$rules=array();
				
				foreach($this->$field->rules() as $rule)
				{
					if($rule == 'required')
					{
						$rules['required']= true;
					}
					elseif($rule=='valid_email'||$rule=='valid_email_rfc')
					{
						$rules['email']   = true;
					}
				}
				$jquery['rules'][$input->name]=$rules;
			}
		}
		echo json_encode($jquery);
	}

}
