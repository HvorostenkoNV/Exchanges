<?php
namespace Main;

trait Singltone
{
	private static $instanceArray = [];

	public static function getInstance()
	{
		if( !array_key_exists(self::class, self::$instanceArray) )
			self::$instanceArray[self::class] = new self;

		return self::$instanceArray[self::class];
	}

	private function __construct()  {}
	private function __clone()      {}
}