<?php
declare(strict_types=1);

namespace Main;
/**************************************************************************************************
 * Singleton trait, provides singleton functional
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
trait Singleton
{
	private static $instanceArray = [];
	/** **********************************************************************
	 * singleton constructor
	 ************************************************************************/
	public static function getInstance()
	{
		if( !array_key_exists(self::class, self::$instanceArray) )
			self::$instanceArray[self::class] = new self;

		return self::$instanceArray[self::class];
	}
	/** closed constructor */
	private function __construct()  {}
	private function __clone()      {}
}