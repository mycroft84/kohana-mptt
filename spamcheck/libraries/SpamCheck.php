<?php
class SpamCheck_Core{
	protected $fields=array();
	
	protected $checks=array();
	
	protected $weights=array();
	
	protected $scores=array();
	
	protected $weighted_scores=array();
	/**
	 * Set add field and value
	 * 
	 * @param string name
	 * @param string value
	 * @return object	 
	 */	
	public function add_field($name,$value)
	{
		$this->fields[$name]=$value;
		
		return $this;
	}
	/**
	 * Get field by name
	 * 
	 * @param string name
	 * @return string	 
	 */		
	public function get_field($name)
	{
		if(isset($this->fields[$name]))
			return $this->fields[$name];
		return '';
	}
	/**
	 * Get all fields
	 * 
	 * @return array	 
	 */		
	public function get_fields()
	{
		return $this->fields;
	}
	/**
	 * Set weight of check
	 * 
	 * @param string name
	 * @param int weight
	 * @return object	 
	 */
	public function set_weight($name,$weight=1)
	{
		$this->weights[$name]=(int) $weight;
		return $this;
	}
	/**
	 * Add check class
	 * @param string name
	 * @param object check class
	 * @return object
	 */
	public function add_check($name,$weight=1)
	{
		$checker='SpamCheck_'.ucfirst($name);
		$this->checks[$name]=new $checker($this);
		$this->set_weight($name,(int) $weight);
		return $this->checks[$name];
	}
	/**
	 * Get check
	 * @param string name
	 * @return object
	 */
	public function get_check($name)
	{
		if(isset($this->checks[$name]))
			return $this->checks[$name];		
	}
	/**
	 * Check agains all checks
	 *
	 * @return  int   spam score 0 bad, 100 good
	 */
	public function check()
	{
		if(empty($this->checks))
			throw new Kohana_Exception('spamcheck.no_checks');
		
		$scores=array();
		$weighted_scores=array();
		
		foreach($this->checks as $check_name=>$check)
		{
			$this->scores[$check_name]			=	$this->calculate_score($check->check());
			
			$this->weighted_scores[$check_name]	=	$this->scores[$check_name]*$this->weights[$check_name];
		}
		//Sum of weighted scores		
		$weighted_sum=array_sum($this->weighted_scores);
		//Number of checks
		$num_checks=count($this->checks);
		//Average score
		$avg_score=$weighted_sum/$num_checks;
		//Max score
		$avg_score= $avg_score > 100 ? 100 : $avg_score;
		//Min score
		$avg_score= $avg_score < -100 ? -100 : $avg_score;
		
		return $avg_score;
	}
	/**
	 * Calculate score of the check
	 *
	 * @param   mixed  score
	 * @return  int   number of rows deleted
	 */
	public function calculate_score($result)
	{
		if(is_bool($result))
		{
			return $result==true ? 100 : -100; 
		}
		return (int) $result;
	}
	/**
	 * Get weighted scores after check
	 * @return array weighted scores
	 */
	public function get_weighted_scores(){
		return $this->weighted_scores;
	}
	/**
	 * Like check() only returns boolean (score below zero is spam, above not)
	 * @return boolean
	 */
	public function is_spam($threshold=0)
	{
		if($this->check() < $threshold)
			return true;
		return false;
	}
}	
?>