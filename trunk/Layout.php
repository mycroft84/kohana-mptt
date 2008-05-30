<?php
class Layout_Core {

	protected static $instance;
		
	protected $layout='layouts/layout';
	
	protected $layout_view;
	
	protected $render_layout=TRUE;
	/**
	 * Singleton instance
	 * @return object singleton instance
	 * @param $layout Object[optional]
	 */
	public static function instance($layout=FALSE)
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
		
		$this->layout_view=new View;
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
			$this->layout_view->set_filename($this->layout);
			$this->layout_view->content=Kohana::$instance->view;
			$this->layout_view->render(true);
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
		return (Router::$controller_path.Router::$controller.'/'.Router::$method);
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
	 * @param $name 
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
			return $this->layout_view->$name;
		}
	}
	/**
	 * Magic __set method
	 * @return 
	 * @param $name 
	 * @param $value 
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
			$this->layout_view->$name=$value;
		}
	}
	/**
	 * Disable layout
	 */
	public function disable_layout()
	{
		$this->render_layout=false;
	}
	/**
	 * Enable layout
	 */
	public function enable_layout()
	{
		$this->render_layout=true;
	}
}

