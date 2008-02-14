<?php
class Jquery_Validation_Core {
	
	protected $jquery;
	
	protected $form;
	
	public static function factory($form=null)
	{
		return new Jquery_Validation($form);
	}
	public function __construct($form =null)
	{
		if(!empty($form) && $form instanceof Forge)
		{
			$this->load($form);
		}
	}
	public function load(Forge $form)
	{
		$this->form=$form;
		
		foreach ($form->inputs as $field=> $input)
		{
			if($input->name!='')
			{
				$rules    = array();
				$messages = array();
				foreach($form->$field->rules() as $rule)
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
						$messages['required']=$this->form->error_message('required',null);
					}
					elseif($rule=='valid_email'||$rule=='valid_email_rfc')
					{
						$rules['email']   = true;
						$messages['email']=$this->form->error_message('valid_email',null);
					}
					elseif($rule=='length')
					{
						if(empty($params[1]))
						{
							$rules['rangelength']=array($params[0],$params[0]);	
							$messages['rangelength']=$this->form->error_message('exact_length',array($params[0]));				
						}
						else
						{
							$rules['minlength']=$params[0];
							$rules['maxlength']=$params[1];
							
							$messages['rangelength']=$this->form->error_message('range',array($params[0],$params[1]));														
						}
						
					}
					elseif($rule=='valid_url')
					{
						$rules['url']=true;
					}
					elseif($rule=='valid_digit')
					{
						$rules['digits']=true;
					}
					elseif($rule=='valid_numeric')
					{
						$rules['number']=true;
					}
				}
				$jquery['rules'][$input->name]=$rules;
				$jquery['messages'][$input->name]=$messages;		
				$this->jquery=$jquery;		
			}
		}
	}
	public function as_array()
	{
		return $this->jquery;
			
	}
	public function as_json()
	{
		return json_encode($this->jquery);
	}
	public function jquery_validation(){

		return '$().ready(function() {$("#'.$this->form->get_attr('id').'").validate('.$this->as_json().');});';
	 
	}
	public function __toString()
	{
		return	$this->jquery_validation();
	}

}
