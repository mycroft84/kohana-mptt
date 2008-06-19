<?php
class ORM_Mptt_Core{
	
	protected $model;
	
	protected $class;
	
	protected $table;
	
	protected $left_column     =    'lft';
	
	protected $right_column    =    'rgt';
	
	protected $parent_column   =    'parent_id';
	
	protected $scope_column    =    'scope';
	
	public function __construct(ORM $model)
	{
		$this->model=$model;
		$this->class=get_class($model);
		
		isset(Kohana::$instance->db) or Kohana::$instance->db = Database::instance();
		
		$this->db=Registry::get('db');
		
		$this->table= inflector::plural(strtolower(substr($this->class, 0, -6)));
	}	
	/**
	 * Locks tree table
	 * This is a straight write lock - the database blocks until the previous lock is released
	 */
	public function lock_tree($aliases = array())
	{
		$sql = "LOCK TABLE " . $this->table . " WRITE";
		return $this->db->query($sql);
	}

	/**
	 * Unlocks tree table
	 * Releases previous lock
	 */
	public function unlock_tree()
	{
		$sql = "UNLOCK TABLES";
		return $this->db->query($sql);
	}	
	/**
	* Get the path leading up to the current node
	* @return array with ORM objects
	* 
	*/
	public function get_path()
	{

		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		
		//$this->model->where($this->scope_column,$this->get_scope());
		$this->model->where($lft_col . ' <= '.$this->model->$lft_col . ' AND ' . $rgt_col . ' >=' .$this->model->$rgt_col . ' ORDER BY '.$lft_col);

		return $this->model->find_all();        

	}
	/**
	* Returns the root node
	* @return array $resultNode The node returned
	*/
	function get_root()
	{   
		$this->model->where('`'.$this->left_column . '` = 1 ');
		return $this->model->find(FALSE,true);
	} 	
	/**
	* Same as insertNewChild except the new node is added as the last child
	* @param array $parentNode The node array of the parent to use
	* 
	* @return 
	*/
	function insert_as_last_child_of($parent_node) 
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		$scp_col=$this->scope_column;              
		$parent_column=$this->parent_column;        
		
		//Set parent id (id of the parent, is childs parent id)          
		//$this->$parent_column=$parent_node->id;
		
		$this->model->$lft_col=$parent_node->$rgt_col;
		$this->model->$rgt_col=$parent_node->$rgt_col+1;
	//	$this->$scp_col=$this->get_scope();                
		
		$this->lock_tree();		
		$this->modify_node($this->model->$lft_col,2);
		//$this->save_node();
		$this->model->save();
		$this->unlock_tree();
		
		return $this;
	}  

	/**
	* Test to see if node has children
	* 
	* @return boolean
	*/
	function has_descendants()
	{ 
		return (($this->model->{$this->right_column} - $this->model->{$this->left_column}) > 1);
	}		
	protected function modify_node($node_int, $changeVal)
	{
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
	//	$table          =       $this->table;
		$scope_col      =        $this->scope_column;
		
		$sql =  "UPDATE     ".$this->table." " .
				"SET        $leftcol = $leftcol + $changeVal ".
				"WHERE      $leftcol >= $node_int";
				//	AND ".$this->scope_column.' = '.$this->$scope_col.';';
		
		$this->db->query($sql);
		
		$sql =  "UPDATE     ".$this->table." " .
				"SET        $rightcol = $rightcol + $changeVal ".
				"WHERE      $rightcol >= $node_int";
//				AND ".$this->scope_column.' = '.$this->$scope_col.';';
		
		$this->db->query($sql);
		
		
		return true;
	}		
}