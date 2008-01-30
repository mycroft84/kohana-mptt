<?php

class Forge extends Forge_Core {
	
	public function __construct($action = '', $title = '', $method = NULL, $class = 'form')
	{
		parent::__construct($action,$title,$method,$class);
		
	}	
	public function jquery_validation($debug=false){
		$return	= '<script type="text/javascript>"';
		$return.= '$().ready(function() {$("#'.$this->attr['id'].'").validate('.$this->jquery_validation_object().');});';
		$return.= '</script>';
		return $return;
	}
	public function jquery_validation_object($array=false)
	{
		$jquery= array();
		foreach ($this->inputs as $field=>$input)
		{
			if($input->name!='')
			{
				$rules    = array();
				$messages = array();
				foreach($this->$field->rules() as $rule)
				{
					
					// Handle params
					$params = FALSE;
					
					if (preg_match('/([^\[]*+)\[(.+)\]/', $rule, $match))
					{
						$rule   = $match[1];
						$params = preg_split('/(?<!\\\\),/', $match[2]);
						$params = str_replace('\,', ',', $params);
					}				
					
					$rule=strtolower($rule);
					
					if($rule == 'required')
					{
						$rules['required']= true;
						$messages['required']=$this->error_message('required',null);
					}
					elseif($rule=='valid_email'||$rule=='valid_email_rfc')
					{
						$rules['email']   = true;
						$messages['email']=$this->error_message('valid_email',null);
					}
					elseif($rule=='length')
					{
						if(empty($params[1]))
						{
							
							$rules['minLength']=$params[0];
							$messages['minLength']=$this->error_message('exact_length',array($params[0]));
							$rules['maxLength']=$params[0];
							$messages['maxLength']=$this->error_message('exact_length',array($params[0]));							
						}
						else
						{
							$rules['minLength']=$params[0];
							$messages['minLength']=$this->error_message('min_length',array($params[0]));	
							$rules['maxLength']=$params[1];
							$messages['maxLength']=$this->error_message('max_length',array($params[1]));														
						}
						
					}
					elseif($rule=='valid_url')
					{
						$rules['url']=true;
					}	

				}
				$jquery['rules'][$input->name]=$rules;
				$jquery['messages'][$input->name]=$messages;
			}
		}
		if($array==true)
			return $jquery;
			
		return json_encode($jquery);
	}
	public function form_id($id){
		// Set form attributes
		$this->attr['id'] = $id;
	}
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

}
