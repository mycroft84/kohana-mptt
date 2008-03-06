<?php
class Forge_Jquery_Validation_Core extends ArrayObject {
	
	protected $form;
	//Factory, useful for chaining
	public static function factory($form=null)
	{
		return new Forge_Jquery_Validation($form);
	}
	//takes forge object as argument
	public function __construct($form =null)
	{
		if(!empty($form) && $form instanceof Forge)
		{
			$this->load($form);
		}
	}
	//loads Forge into jquery validation array
	public function load(Forge $form)
	{
		
		$this->form=$form;
		
		foreach ($this->form->inputs as $field=> $input)
		{
			
			if($input->name!='')
			{
				$rules    = array();
				$messages = array();
				
				foreach($this->form->$field->rules() as $rule)
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
					
					if($input->label==null)
					{
						$field_name=$input->name;
					}
					else
					{
						$field_name=$input->label;
					}
					$field_name=strtolower($field_name);
					
					//check all Kohana rules and match them with jQuery validate rules
					if($rule == 'required')
					{
						$rules['required']= true;
						$messages['required']=$this->form->error_message('required',$field_name);
					}
					elseif($rule=='valid_email'||$rule=='valid_email_rfc')
					{
						$rules['email']   = true;
						$messages['email']=$this->form->error_message('valid_email',$field_name);
					}
					elseif($rule=='length')
					{
						if(empty($params[1]))
						{
							$rules['rangelength']=array($params[0],$params[0]);	
							$messages['rangelength']=$this->form->error_message('exact_length',array($field_name,$params[0]));				
						}
						else
						{
							$rules['minlength']=$params[0];
							$rules['maxlength']=$params[1];
							
							$messages['rangelength']=$this->form->error_message('range',array($field_name,$params[0],$params[1]));														
						}
						
					}
					elseif($rule=='valid_url')
					{
						$rules['url']=true;
						$messages['url']=$this->form->error_message('valid_url',$field_name);
					}
					elseif($rule=='valid_digit')
					{
						$rules['digits']=true;
						$messages['url']=$this->form->error_message('valid_type',$field_name);
					}
					elseif($rule=='valid_numeric')
					{
						$rules['number']=true;
						$messages['url']=$this->form->error_message('valid_type',$field_name);
					}
				}
				$jquery['rules'][$input->name]=$rules;
				$jquery['messages'][$input->name]=$messages;		

				
				
				$this->set_spl($jquery);
				
			}
		}
		return $this;
		
	}
	//Do the spl magic with the array
	public function set_spl($jquery_array)
	{
		//spl magic
		$this->exchangeArray($jquery_array);
		$this->setFlags(ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);		
	}
	//returns rules and messages as array
	public function as_array()
	{
		return $this->getArrayCopy();
	}
	//Load an array if you want to bypass Forge
	public function load_array(array $array)
	{
		$this->set_spl($array);
		return $this;
	}
	//returns all rules and messages as json ready to be fed to jquery validation
	public function as_json()
	{
		return json_encode($this->as_array());
	}
	//Returns string which does the whole validation
	public function jquery_validation(){

		return '$().ready(function() {$("#'.$this->form->get_attr('id').'").validate('.$this->as_json().');});';
	 
	}
	//proxy to jquery_validation()
	public function __toString()
	{
		return	$this->jquery_validation();
	}

}
