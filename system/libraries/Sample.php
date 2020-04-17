<?php

class Sample {
	
	private $param;

	public function __construct()
	{
		$this->param = 'Hello';	
	}

	public function test()
	{
		print_r($this->param);
	}
}