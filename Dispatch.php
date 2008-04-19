<?php

class Dispatch_Core{
	
	protected $controller;
	
	public static function controller($controller)
	{
		$controller_file=strtolower($controller);
		
		// Set controller class name
		$controller = ucfirst($controller).'_Controller';
		
		if(!class_exists($controller, FALSE))
		{
			// If the file doesn't exist, just return
			if (($filepath = Kohana::find_file('controllers', $controller_file)) === FALSE)
				return FALSE;
				
			// Include the Controller file
			require_once $filepath;
		}
		
		// Run system.pre_controller
		Event::run('dispatch.pre_controller');
		
		// Initialize the controller
		$controller = new $controller;

		// Run system.post_controller_constructor
		Event::run('dispatch.post_controller_constructor');		
					
		return new Dispatch($controller);
	}
	public function __construct(Controller $controller)
	{
		$this->controller=$controller;
		
	}
	public function __call($name,$arguments=null)
	{
		if(method_exists($this->controller,$name))
		{
			return $this->method($name,$arguments);
		}
		return false;
	}
	public function method($method,$arguments=null)
	{
		if(!method_exists($this->controller,$method))
			return false;
			
		if (method_exists($this->controller,'_remap'))
		{
			// Make the arguments routed
			$arguments = array($method, $arguments);

			// The method becomes part of the arguments
			array_unshift($arguments, $method);

			// Set the method to _remap
			$method = '_remap';
		}	
				
		ob_start();
		
		if(is_string($arguments))
		{
			$arguments=array($arguments);
		}
			
		switch(count($arguments))
		{
			case 1:
				$this->controller->$method($arguments[0]);
			break;
			case 2:
				$this->controller->$method($arguments[0], $arguments[1]);
			break;
			case 3:
				$this->controller->$method($arguments[0], $arguments[1], $arguments[2]);
			break;
			case 4:
				$this->controller->$method($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
			break;
			default:
				// Resort to using call_user_func_array for many segments
				call_user_func_array(array($this->controller, $method), $arguments);
			break;
		}		
		
		// Run system.post_controller
		Event::run('dispatch.post_controller');
					
		$buffer=ob_get_contents();
		
		ob_end_clean();
		
		return $buffer;
	}
	
}