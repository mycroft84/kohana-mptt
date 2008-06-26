<?php
abstract class SpamCheck_Abstract{
	protected $spamcheck;
	
	public function __construct(SpamCheck $spamcheck)
	{
		$this->spamcheck=$spamcheck;
	}
	public function check(){}
}
?>