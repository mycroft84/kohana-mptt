<?PHP

class Module_Core{

	//Array of registered modules
	protected static $modules=array();
	
	//Module name
	public $module_name;
	
	//Module path
	public $module_path;
	
	//Load all registered modules into include_paths (or not)
	public static $load_registered_modules=false;
	
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
        
		Event::run('module.post_routing');
		
		$paths=array();
		if(self::$load_registered_modules==true)
		{
			foreach(self::$modules as $module)
			{
				$paths[]=$module->module_path;
			}
		}
		else
		{
			$paths[]=$this->module_path;
		}
		//Load triggered (or all) module into include paths so views, models and libraries can be loaded the normal way.
		Config::set('core.modules',array_merge(Config::item('core.modules'),$paths));	

	}
	public function find_file($directory, $filename, $required = FALSE, $ext = FALSE, $use_cache = TRUE)
	{
		static $found = array();

		$search = $directory.'/'.$filename;
		$hash   = sha1($search.$ext);

		if ($use_cache AND isset($found[$hash]))
			return $found[$hash];
					
		if ($directory == 'config' OR $directory == 'i18n' OR $directory === 'l10n')
		{
			$fnd = array();

			if (is_file($this->module_path.'/'.$search.EXT)) $fnd[] = $this->module_path.'/'.$search.EXT;

			// If required and nothing was found, throw an exception
			if ($required == TRUE AND $fnd === array())
				throw new Kohana_Exception('core.resource_not_found', Kohana::lang('core.'.inflector::singular($directory)), $filename);

			return $found[$hash] = $fnd;
		}
		else
		{
			// Users can define their own extensions, css, xml, html, etc
			$ext = ($ext == FALSE) ? EXT : '.'.ltrim($ext, '.');

			if (is_file($this->module_path.'/'.$search.$ext))
				return $found[$hash] = $this->module_path.'/'.$search.$ext;
			

			// If the file is required, throw an exception
			if ($required == TRUE)
				throw new Kohana_Exception('core.resource_not_found', Kohana::lang('core.'.inflector::singular($directory)), $filename);

			return $found[$hash] = FALSE;
		}				
	}
}