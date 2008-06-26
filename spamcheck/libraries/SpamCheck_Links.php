<?php
class SpamCheck_Links extends SpamCheck_Abstract{

	protected $max_links=2;
	
	protected $power=2;
	
	public function set_max_links($count)
	{
		$this->max_links=$count;
		return $this;
	}
	public function set_link_penalty($power)
	{
		$this->power=$power;
		return $this;
	}
	/*
	 * Check the comment for being spam
	 */
	public function check(){
		$content=strtolower($this->spamcheck->get_field('content'));
		
		//Algorithm from Geert_DD		
		$count = substr_count($content, 'http://');
		$count = $count+substr_count($content, 'https://');
		
		return ($count < $this->max_links) ? 100 : -1*((int) pow($count, $this->power));
		
	}
}