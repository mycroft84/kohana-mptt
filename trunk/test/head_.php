<?php

class Head_Core extends ArrayObject{
	// Head singleton
	private static $instance;	
	/**
	 * Head instance of Head.
	 */
	public static function instance()
	{
		// Create the instance if it does not exist
		empty(self::$instance) and new Head;

		return self::$instance;
	}
		
	public function __construct()
	{
		$this['title']		=new Head_Title;
		$this['base']		=new Head_Base;
		$this['javascript']	=new Head_Javascript;
		//$this['meta']		=new Head_Partial;
		$this['css']		=new Head_Css;
		$this['link']		=new Head_Link;
		
		$this->setFlags(ArrayObject::ARRAY_AS_PROPS );
		// Singleton instance
		self::$instance = $this;
	}
	public function __tostring()
	{
		return (string) $this->render();
	}	
	public function render()
	{
		$html='';
		foreach($this as $field)
		{
			$html.=$field->render();
		}
		return $html;
	}
	public function append_script_file($file)
	{
		$this['javascript']['files'][]=$file;
	}	
	public function append_script_src($script)
	{
		$this['javascript']['scripts'][]=$script;
	}	
	public function append_css_file($file,$type='screen')
	{
		$this['css']['files'][]=array($file,$type);
		return $this;
	}
	public function append_css_style($script)
	{
		$this['css']['styles'][]=$script;
		return $this;
	}	
	public function append_link($link,$rel='alternate',$type='application/rss+xml')
	{
		$this['link'][]=array($link,$rel,$type);
	}	
}

class Head_Partial extends Head_Core{
	
	public function __construct()
	{
		$this->setFlags(ArrayObject::ARRAY_AS_PROPS);
	}

	
}
class Head_Title extends Head_Partial{
	public function set($title)
	{
		$this['title']=$title;
	}
	public function render()
	{
		return (string) '<title>'.$this['title'].'</title>'."\n\r";
	}
}
class Head_Base extends Head_Partial{
	public function set($base_href)
	{
		$this['base_href']=$base_href;
	}
	public function render()
	{
		return (string) '<base href="'.$this['base_href'].'" />'."\n\r";
	}
}
class Head_Javascript extends Head_Partial{
	
	public function __construct()
	{
		$this->setFlags(ArrayObject::ARRAY_AS_PROPS);
		$this['files']=array();
		$this['scripts']=array();
	}
	public function render()
	{
		$html='';
		foreach($this as $type)
		{
			if($type=='files')
			{
				foreach($this['files'] as $field)
				{
					$html.=html::script($field);
				}
			}
			else
			{
				foreach($this as $script)
				{
					$html.='<script type="text/javascript">'.$script.'</script>'."\r\n";
				}				
			}
		}
		return $html;
	}
}
class Head_Css extends Head_Partial{
	
	public function __construct()
	{
		$this->setFlags(ArrayObject::ARRAY_AS_PROPS);
		$this['files']=array();
		$this['styles']=array();
	}
	public function render()
	{
		$html='';
		foreach($this as $type)
		{
			if($type=='files')
			{
				foreach($this['files'] as $field)
				{
					$html.=html::stylesheet($field[0],$field[1]);
				}
			}
			else
			{
				foreach($this['styles'] as $script)
				{
					$html.='<style type="text/css">'.$script.'</style>'."\r\n";
				}				
				
			}
		}

		return $html;
	}
}
class Head_Link extends Head_Partial
{
	public function render()
	{
		$html='';
		foreach($this as $link)
		{
			$html.=html::link($link[0],$link[1],$link[2]);
		}
		return $html;		
		
	}
	
}