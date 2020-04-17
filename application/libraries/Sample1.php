<?php

class Sample1 extends Sample {

	public function __construct()
	{
		parent::__construct();
	}

	public function test1()
	{
		print_r($this->test());
	}
}