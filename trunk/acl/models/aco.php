<?php
/**
 * Khaos :: Khacl
 * 
 * @author      David Cole <neophyte@sourcetutor.com>
 * @author      dlib  <dlib@hichte.nl>
 */

class Aco_Model extends ORM{
	protected $acts_as='mptt';
	
	public function create($aco, $parent = null, $link = null){
		
		$rs=self::$db->getwhere($this->table, array('name' => $aco), 1);
		
		if($rs->count()>0)
			return false;
		
		$link=is_numeric($link)?$link:'NULL';
		

		if($parent==null)
		{
			$rs=self::$db->orderby('rgt','desc')->get($this->table,1);
			if($rs->count()==0)
			{
				$right=0;
			}
			else
			{
				$right=$rs->current()->rgt;
			}
			return self::$db->insert($this->table,array('lft' => ($right + 1), 'rgt' => ($right + 2), 'name' => $aco, 'link' => $link));
		}
		else
		{
                /*
                 * Parent is specified so we have to update all records
                 * which are futher down the tree than the parent.
                 */
                
                // Grab the left value of the specified parent
                $rs = self::$db->getwhere($this->table, array('name' => $parent), 1);
                
				$parent=ORM::factory('aco')->find_by_name($parent);
				
				if(!$parent->exists())
					return false;
				       
				$aco_node=new Aco_Model;
				
				$aco_node->name=$aco;
				$aco_node->link=$link;
				
				$aco_node->insert_as_last_child_of($parent);
				
		}		
		return true;
	}
	public function delete($aco)
	{
		if(!($rs =  self::$db->getwhere($this->table, array('name' => $aco), 1)))
			return false;
			
		if($rs->count()===0)
			return false;
			
        // delete ARO cache
        if (Config::item('acl.cache')==true)
		{
			Cache::instance()->delete_tag('acl');
		}
        
		$tables=Config::item('acl.tables');
		/*
		 * Delete the aRO
		 */
		$left=$rs->current()->lft;
		$right = $rs->current()->rgt;
		$width = ($right - $left) + 1;
		
		$prefix=self::$db->table_prefix();
		
		$this->lock_tree();	
		self::$db->query('DELETE '.$prefix.$tables['acos'].',
                                      '.$prefix.$tables['access'].',
                                      '.$prefix.$tables['access_actions'].'
                                 FROM '.$prefix.$tables['acos'].'
                                   LEFT JOIN '.$prefix.$tables['access'].' ON '.$prefix.$tables['acos'].'.id = '.$prefix.$tables['access'].'.aco_id
                                   LEFT JOIN '.$prefix.$tables['access_actions'].' ON '.$prefix.$tables['access'].'.id = '.$prefix.$tables['access_actions'].'.access_id
                                 WHERE '.$prefix.$tables['acos'].'.lft BETWEEN '.$left.' AND '.$right);
        
        self::$db->set('rgt', new Database_Expr('rgt - '.$width));
        self::$db->where('rgt >', $right);
        self::$db->update($this->table);
        
        self::$db->set('lft', new Database_Expr('lft - '.$width));
        self::$db->where('lft >', $right);
        self::$db->update($this->table);
		$this->unlock_tree();			
	}
	
	
}