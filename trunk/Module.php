<?PHP

class Module_Core{

	//Array of registered modules
	public static $modules=array();
	
	//Module name
	public $module_name;
	
	//Module path
	public $module_path;
	
	//Hook for the system.post_routing event
	public static function hook()
	{
		if(Router::$controller==null AND self::is_registered(Router::$rsegments[0]))
		{
			$module=self::get(Router::$rsegments[0]);
			$module->route();	
		}		
	}
	//Register new modules
	public static function register($name,$path)
	{
		self::$modules[$name]=new Module($name,$path);
	}
	//Register all directories in a directory as modules
	public static function register_dir($path)
	{
		$items=(Kohana::list_files($path));
		foreach($items as $item)
		{
			if(is_dir($item))
			{
				$pos=strlen($item)-strrpos($item,'/')-1;
				$name= substr($item,-$pos );
				self::register($name,$item);
			}
		}
	}
	//Check whether module is registered
	public static function is_registered($name)
	{
		return array_key_exists($name,self::$modules);
	}
	//get instance of module
	public static function get($name)
	{
		return self::$modules[$name];
	}	
	//
	protected function __construct($name,$path)
	{
		$path=rtrim($path,'/');
		$this->module_name=$name;
		$this->module_path=$path;
	}
	//Routing method to find the controller in the module directory
	public function route()
	{
		Router::$directory  = $this->module_path.'/controllers/';
		Router::$controller = isset(Router::$rsegments[1]) ? Router::$rsegments[1] : Config::item('routes._default');
		Router::$method     = isset(Router::$rsegments[2]) ? Router::$rsegments[2] : 'index';
		Router::$arguments  = isset(Router::$rsegments[3]) ? array_slice(Router::$rsegments, 3) : array();	
        
		//Load triggered module into include paths so views, models and libraries can be loaded the normal way.
		//Only current module is loaded into include path, can be altered so all registered modules are loaded
		Config::set('core.modules',array_merge(Config::item('core.modules'),array($this->module_path)));		
	}
}