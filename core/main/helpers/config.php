<?php
namespace Main\Helpers;

use Main\Singltone;

class Config
{
	use Singltone;

	private function __construct()
	{

	}

	public function getParam(string $paramName) : string
	{
		return $paramName;
	}
}