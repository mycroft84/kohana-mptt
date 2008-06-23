<?php
/**
 * Khaos :: Khacl
 * 
 * @author      David Cole <neophyte@sourcetutor.com>
 * @author      dlib  <dlib@hichte.nl>
 */

class Aro_Model extends ORM{
	protected $acts_as='mptt';
	
	protected $tables;
	
	public function create($aro, $parent = null, $link = null){
		$rs=self::$db->getwhere($this->table, array('name' => $aro), 1);

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
			return self::$db->insert($this->table,array('lft' => ($right + 1), 'rgt' => ($right + 2), 'name' => $aro, 'link' => $link));
		}
		else
		{
                /*
                 * Parent is specified so we have to update all records
                 * which are futher down the tree than the parent.
                 */
                
                // Grab the left value of the specified parent
                $rs = self::$db->getwhere($this->table, array('name' => $parent), 1);
                
				$parent=ORM::factory('aro')->find_by_name($parent);
				
				if(!$parent->exists())
					return false;
				       
				$aro_node=new Aro_Model;
				
				$aro_node->name=$aro;
				$aro_node->link=$link;
				
				$aro_node->insert_as_last_child_of($parent);
				
		}		
		return true;
	}
	public function delete($aro)
	{
		if(!($rs =  self::$db->getwhere($this->table, array('name' => $aro), 1)))
			return false;
			
		if($rs->count()===0)
			return false;
			
        // delete ARO cache
        if (Config::item('acl.cache')==true)
		{
			$cache=new Cache;
			$cache->delete('acl_'.$aro);
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
		self::$db->query('DELETE '.$prefix.$tables['aros'].',
                                      '.$prefix.$tables['access'].',
                                      '.$prefix.$tables['access_actions'].'
                                 FROM '.$prefix.$tables['aros'].'
                                   LEFT JOIN '.$prefix.$tables['access'].' ON '.$prefix.$tables['aros'].'.id = '.$prefix.$tables['access'].'.aro_id
                                   LEFT JOIN '.$prefix.$tables['access_actions'].' ON '.$prefix.$tables['access'].'.id = '.$prefix.$tables['access_actions'].'.access_id
                                 WHERE '.$prefix.$tables['aros'].'.lft BETWEEN '.$left.' AND '.$right);
        
        self::$db->set('rgt', new Database_Expr('rgt - '.$width));
        self::$db->where('rgt >', $right);
        self::$db->update($this->table);
        
        self::$db->set('lft', new Database_Expr('lft - '.$width));
        self::$db->where('lft >', $right);
        self::$db->update($this->table);
		$this->unlock_tree();	
	}
}
?>
