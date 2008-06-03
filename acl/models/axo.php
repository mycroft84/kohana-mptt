<?php
class Axo_Model extends ORM{
	
	public function create($axo)
	{
        /*
         * Ensure there is no other AXO
         * in the database by this name
         */
        
        
        $rs = self::$db->getwhere($this->table, array('name' => $axo));
        
        if ($rs->count() === 0)
        {
            // Create new AXO
            return self::$db->insert($this->table, array('name' => $axo));
        }
        else 
           return false;   		
	}
	public function delete($axo)
	{
		if(!($rs=self::$db->getwhere($this->table,array('name' => $axo), 1)))
			return false;
			
		if($rs->count()===0)
			return false;
		else
		{
			$axo_id=$rs->current->id;
		}
		
		if(Config::item('acl.cache'))
		{
			Cache::instance()->delete_tag('acl');			
		}
		$tables=Config::item('acl.tables');
		self::$db->limit(1);
		self::$db->delete($tables['access_actions'],array('axo_id' => $axo_id));
		
		self::$db->limit(1);
		self::$db->delete($tables['axos'],array('id' => $axo_id));		
		
	}
}
