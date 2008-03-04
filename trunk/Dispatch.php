<?php

class Dispatch_Core{
	
	protected $controller;
	
	public static function controller($controller)
	{
		
		// If the file doesn't exist, just return
		if (($filepath = Kohana::find_file('controllers', $controller)) === FALSE)
			return FALSE;
			
		// Include the Controller file
		require $filepath;

		// Set controller class name
		$controller = ucfirst($controller).'_Controller';

		// Make sure the controller class exists
		class_exists($controller, FALSE) or Event::run('system.404');


		// Initialize the controller
		$controller = new $controller;
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
		
		
		$buffer=ob_get_contents();
		
		ob_end_clean();
		
		return $buffer;
	}
	
}