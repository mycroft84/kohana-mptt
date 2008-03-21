<?php
class Model_Formation_Core extends Formation{
	
	//carries name of the model or instance of it
	protected $_model;

	//Which fields to exclude 
	protected $exclude=array('id');
	
	//Which fields to include
	protected $form_fields=array();
	
	protected $disabled=array();
	
	public function __construct($model=false)
	{
		parent::__construct();
		
		//If $model is false, create empty model,
		//if it is not an object use $argument to find a model (pass id for example)
		//else the model passed is an object 
		if($model==false)
		{
			$this->_model=new $this->_model;
		}
		elseif(!is_object($model))
		{
			$this->_model= new $this->_model($model);
		}
		else
		{
			$this->_model=$model;				
		}
	
		$this->build_form();
	}
	/**
	 * Build form
	 *
	 * @param boolean $guess_fields, automatic field determination
	 */
	protected function build_form()
	{
		//Get field types and rules, filters everything
		$validate=$this->_model->get_validate();
				
		foreach($this->_model->list_fields() as $name=>$property)
		{
			if(in_array($name,$this->exclude))
				continue;

			if($this->form_fields!=array() and !in_array($name,$this->form_fields))
				continue;
			
			//By default all fields are input
			$type='input';


			if(isset($validate[$name]['type']))
			{
				$this->add_element($validate[$name]['type'],$name);
			}
			else
			{
				//Try to guess field type given the database information
				if($property['type']=='string' AND !isset($property['length']))
				{
					$type='textarea';
				}
				if(isset($property['format'])&&$property['format']=='0000-00-00 00:00:00')
				{
					$type='input';
				}
	
				//Adding elements
				$this->add_element($type,$name);				
			}

			if(in_array($name,$this->disabled))
			{
				$this[$name]->set_attr('disabled','disabled');
			}
			if(isset($validate[$name]['pre_filters']))
			{
				foreach($validate[$name]['pre_filters'] as $filter)
				{
						$this[$name]->add_pre_filter($filter);
				}
			}				
			if(isset($validate[$name]['rules']))
			{
				foreach($validate[$name]['rules'] as $rule)
				{
					//array for when you give arguments to a rule
					if(is_array($rule))
					{
						$this[$name]->add_rule($rule[0],$rule[1]);
					}else{
						$this[$name]->add_rule($rule);
					}
				}
			}
			if(isset($validate[$name]['callbacks']))
			{
				foreach($validate[$name]['callbacks'] as $callback)
				{
					$this[$name]->add_callback($callback);
				}
			}	
			if(isset($validate[$name]['post_filters']))
			{
				foreach($validate[$name]['post_filters'] as $filter)
				{
						$this[$name]->add_post_filter($filter);
				}
			}	

			//Additional rules retrieved from database
			if(isset($property['length']))
			{
				$this[$name]->add_rule('Rule_Max_Length',$property['length']);
			}
						
			//If model exists at its values to the fields
			if($this->_model->exists())
			{
				$this->set_values($this->_model->as_array());
			}
			
		}

		$this->add_element('submit','Submit');
	}
	/**
	 * Retrieve model, might be handy sometime
	 *
	 * @return unknown
	 */
	public function model()
	{
		return $this->_model;
	}
	/**
	 * Save form, validate first
	 *
	 * @param unknown_type $commit
	 * @return unknown
	 */
	public function save($commit=true){
		
		if($this->validate())
		{
			$this->_model->load_values($this->as_array());
			if($commit==true)
			{
				return $this->_model->save();
			}
			return $this->_model;
		}
		return false;
	}
}