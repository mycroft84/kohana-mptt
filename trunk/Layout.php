<?php
class Layout_Core {

	protected static $instance;
		
	protected $layout='layouts/layout';
	
	protected $view;
	
	protected $render_layout=true;
	/**
	 * Singleton instance
	 * @return object singleton instance
	 * @param $layout Object[optional]
	 */
	public static function instance($layout=false)
	{
        if(is_null(self::$instance))
        {
            self::$instance = new self($layout);
        }
        return self::$instance;	
	}
	/**
	 * Constructor
	 * @return 
	 * @param $layout Object
	 */
	private function __construct($layout)
	{
		$layout!=FALSE AND $this->layout=$layout;
		
		Event::add('system.post_controller',array($this,'render'));
		
		$this->view=new View;
		Kohana::$instance->view=new View;
		
	}

	/**
	 * Render layout, called automatically after controller
	 * Also renders the subview into $content 
	 *
	 *  @return void
	 */
	public function render()
	{	
		if(Kohana::$instance->view instanceof View AND Kohana::$instance->view->kohana_filename==NULL)
		{
			Kohana::$instance->view->set_filename($this->view_path());
		}				
		
		if($this->render_layout==true)
		{	
			$this->view->set_filename($this->layout);
			$this->view->content=Kohana::$instance->view;
			$this->view->render(true);
		}
		else
		{			
			if(Kohana::$instance->view instanceof View AND Kohana::$instance->view->kohana_filename!=NULL)
			{
				Kohana::$instance->view->render(true);
			}
		}
	}
	/**
	 * Returns the standard controller to view mapping
	 * views/controller_path/controller_name/controller_method
	 *
	 * @return string
	 */
	public function view_path()
	{
		return (Router::$controller_dir.'/'.Router::$controller.'/'.Router::$method);
	}
	/**
	 * calls render()
	 *
	 * @return unknown
	 */
	public function __toString()
	{
		return (string) $this->render();
	}
	/**
	 * Magic __get method
	 * @return mixed
	 * @param $name Object
	 */
	public function __get($name)
	{
		if(isset($this->$name))
		{
			return $this->$name;
		}
		elseif($name=='view')
		{
			return Kohana::$instance->view;
		}		
		else
		{
			return $this->view->$name;
		}
	}
	/**
	 * Magic __set method
	 * @return 
	 * @param $name Object
	 * @param $value Object
	 */
	public function __set($name,$value)
	{
		if(isset($this->$name))
		{
			$this->$name=$value;
		}
		elseif($name=='view')
		{
			Kohana::$instance->view=$value;
		}
		else
		{
			$this->view->$name=$value;
		}
	}
}

