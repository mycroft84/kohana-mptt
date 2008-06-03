<?php
class Acl{
	
	protected $db;
	
	protected static $tables=array();
	
	protected static $cache;
	
	public function __construct(){
		
		if(empty(self::$tables))
			self::$tables=Config::item('acl.tables');
		
		self::$cache=Config::item('acl.cache');
		
		isset(Kohana::$instance->db) or Kohana::$instance->db = Database::instance();
		
		$this->db=Kohana::$instance->db;
		
        // Instantiate the ARO, ACO and AXO objects
        $this->aro = new Aro_Model();
		$this->aco = new Aco_Model();
		$this->axo = new Axo_Model();

	}
	
	public static function check($aro,$aco,$axo=null,$cache=null)
	{
		($cache==null) ? self::$cache=Config::item('acl.cache') : $cache;
		
		if(self::$cache == TRUE)
		{
			$acl_cache=new Cache;
			
			$cache_hash=md5($aco.$axo);
			$aro_cache=array();
			
			$cache_key='acl_'.$aro;
			
			if(($aro_cache=$acl_cache->get($cache_key))!==false)
			{
				if(isset($aro_cache[$cache_hash]))
					return $aro_cache[$cache_hash];
			}
		}
			
			
		
		if(empty(self::$tables))
			self::$tables=Config::item('acl.tables');
						 
		$tables=self::$tables;
		
		isset(Kohana::$instance->db) or Kohana::$instance->db = Database::instance();
		
		$db=Kohana::$instance->db;
		$prefix=$db->table_prefix();
		
		$rs=$db->query('SELECT access.allow
                            FROM ('.$prefix.$tables['aros'].' AS aro_node, '.$prefix.$tables['acos'].' AS aco_node)
                              LEFT JOIN '.$prefix.$tables['aros'].' AS aro_branch ON (aro_node.lft >= aro_branch.lft AND aro_node.lft <= aro_branch.rgt)
                              LEFT JOIN '.$prefix.$tables['acos'].' AS aco_branch ON (aco_node.lft >= aco_branch.lft AND aco_node.lft <= aco_branch.rgt)
                              INNER JOIN '.$prefix.$tables['access'].' AS access ON (aro_branch.id = access.aro_id AND aco_branch.id = access.aco_id)
                            WHERE aro_node.name = ? AND aco_node.name = ?
                            ORDER BY aro_branch.rgt ASC, aco_branch.rgt ASC
                            LIMIT 1', array($aro, $aco));
		if($rs->count()==1)
		{
			$row=$rs->current();
			
			if($row->allow=='Y')
			{
				if($axo!==null)
				{
                $rs = $db->query('SELECT access_actions.allow
                                        FROM ('.$prefix.$tables['aros'].' AS aro_node, '.$prefix.$tables['acos'].' AS aco_node, '.$prefix.$tables['axos'].' AS axo_node)
                                          LEFT JOIN '.$prefix.$tables['aros'].' AS aro_branch ON (aro_node.lft >= aro_branch.lft AND aro_node.lft <= aro_branch.rgt)
                                          LEFT JOIN '.$prefix.$tables['acos'].' AS aco_branch ON (aco_node.lft >= aco_branch.lft AND aco_node.lft <= aco_branch.rgt)
                                          LEFT JOIN '.$prefix.$tables['access'].' AS access ON (aro_branch.id = access.aro_id AND aco_branch.id = access.aco_id)
                                          INNER JOIN '.$prefix.$tables['access_actions'].' AS access_actions ON (access.id = access_actions.access_id AND axo_node.id = access_actions.axo_id)
                                        WHERE aro_node.name = ? AND aco_node.name = ? AND axo_node.name = ?
                                        ORDER BY aro_branch.rgt ASC, aco_branch.rgt ASC
                                        LIMIT 1', array($aro, $aco, $axo));	
	                if ($rs->count() == 1)
	                {
	                    $allow = (($rs->current()->allow == 'Y')?true:false);
	                }
					// No ((ARO->ACO)->AXO) link exists
	                else
					{
						$allow = false;	
					} 
	                    													
				}
				// ARO -> ACO link is set to allow with no AXO specified
				else
				{
					$allow=true;
				}
			}
			// ARO -> ACO link is set to deny
			else
			{
				$allow=false;	
			}
		}
		// No results matching the specified ARO, ACO combination
		else
		{
			$allow=false;
		}
	    if (self::$cache)
	    {
	        $aro_cache[$cache_hash] = $allow;
			$acl_cache->set($cache_key,$aro_cache,array('acl'));

	    }		
		return $allow;
		
	}
	public function allow($aro,$aco,$axo=null)
	{
		return $this->_set($aro,$aco,$axo,true);
	}
	public function deny($aro,$aco,$axo=null)
	{
		return $this->_set($aro,$aco,$axo,false);
	}	
	protected function _set($aro,$aco,$axo=null,$allow=true)
	{
        // delete ARO cache
        if (self::$cache)
		{
			$cache=new Cache;
			$cache->delete('acl_'.$aro);
		}
					
		$allow=($allow == TRUE)? 'Y' : 'N';
		
		$prefix=$this->db->table_prefix();
		
		$tables=self::$tables;
		
		//Can be moved to model
		//TODO only retrieve 'id'
		if(!($rs=$this->db->getwhere($prefix.$tables['aros'], array('name' => $aro), 1)))
			return false;
		
		if($rs->count()!=1)
			return false;
		
		$aro_id=$rs->current()->id;
		
		if(!($rs=$this->db->getwhere($prefix.$tables['acos'], array('name' => $aco), 1)))
			return false;
		
		if($rs->count()!=1)
			return false;		

		$aco_id=$rs->current()->id;
		 
		if($axo!==null)
		{
			if(!($rs=$this->db->getwhere($prefix.$tables['axos'], array('name' => $axo), 1)))
				return false;
			
			if($rs->count()!=1)
				return false;		
			
			$axo_id=$rs->current()->id;
		}
		 
		//end
		
        /*
         * If needed create/modify the ARO -> ACO map in the access table
         */
        
        if (($rs = $this->db->getwhere($prefix.$tables['access'], array('aro_id' => $aro_id, 'aco_id' => $aco_id))) !== false)
        {
            if ($rs->count() === 0) // Create new link
            {
                if ($axo === null) // No AXO so set the ARO -> ACO access to whatever is set by $allow
                {
                    if (!($rs=$this->db->insert($prefix.$tables['access'], array('aro_id' => $aro_id, 'aco_id' => $aco_id, 'allow' => $allow))))
                        return false;
                }
                else // AXO set so make the ARO -> ACO access to allowed as the ALLOW/DENY will be determined by the AXO later on
                {
                    if (!($rs=$this->db->insert($prefix.$tables['access'], array('aro_id' => $aro_id, 'aco_id' => $aco_id, 'allow' => 'Y'))))
                        return false;                    
                }
                    
                $access_id = $rs->insert_id();
            }
            else // Modify existing link if needed
            {
                $access_id       = $rs->current()->id;
                
                if ($axo === null) // No AXO so update the ARO -> ACO access to whatever is specified by $allow
                {
                    if ($row->allow != $allow)
                        if (!$this->db->update($prefix.$tables['access'], array('allow' => $allow), array('id' => $access_id)))
                            return false;
                }
                else // AXO specified so we set the ARO -> ACO access to allowed as the ALLOW/DENY willbe determined by the AXO later on
                {
                    if (!$this->db->update($prefix.$tables['access'], array('allow' => 'Y'), array('id' => $access_id)))
                        return false;                    
                }
            }
        }
        else 
		{
            return false;				
		}

        /*
         * If needed create/modify the access -> action link in the access_actions table
         */
        
        if ($axo !== null)
        {
            
            if (($rs = $this->db->getwhere($prefix.$tables['access_actions'], array('access_id' => $access_id, 'axo_id' => $axo_id))) !== false)
            {
                if ($rs->count() === 0) // create link
                {
                    if (!$this->db->insert($prefix.$tables['access_actions'], array('access_id' => $access_id, 'axo_id' => $axo_id, 'allow' => $allow)))
                        return false;
                }
                else // Modify existing link 
                {
                    $row = $rs->current();
                    
                    if ($row->allow != $allow)
                        if (!$this->db->update($prefix.$tables['access_actions'], array('allow' => $allow), array('id' => $row->id)))
                            return false;
                }
                
                return true;
            }
            else 
                return false;
        }
        else 
            return true;				
	}
}

