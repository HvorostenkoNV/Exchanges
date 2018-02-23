<?php
namespace Main\Exchange\Procedures;

use
	Throwable,
	RuntimeException,
	Main\Helpers\DB,
	Main\Helpers\Logger;

class Manager
{
	private static
		$DB             = NULL,
		$dbUnavailable  = false;
	/* -------------------------------------------------------------------- */
	/* ------------------------ get procedures list ----------------------- */
	/* -------------------------------------------------------------------- */
	public static function getProceduresList() : array
	{
		$DB     = self::getDB();
		$result = [];

		if( $DB )
			foreach( $DB->query('SELECT NAME FROM procedures WHERE ACTIVITY = ?', [1]) as $itemInfo )
			{
				$procedureNameClass = self::getProcedureClassName($itemInfo['NAME']);
				try
				{
					$result[] = new $procedureNameClass;
				}
				catch( Throwable $error )
				{
					Logger::getInstance()->addWarning('Failed to create procedure object "'.$itemInfo['NAME'].'": '.$error->getMessage());
				}
			}

		return $result;
	}
	/* -------------------------------------------------------------------- */
	/* --------------------------- get procedure -------------------------- */
	/* -------------------------------------------------------------------- */
	public static function getProcedure(string $name) : ?Procedure
	{
		$DB = self::getDB();

		if( $DB && strlen($name) > 0 )
			foreach( $DB->query('SELECT NAME FROM procedures WHERE ACTIVITY = ? AND NAME = ?', [1, $name]) as $itemInfo )
			{
				$procedureNameClass = self::getProcedureClassName($itemInfo['NAME']);
				try
				{
					return new $procedureNameClass;
				}
				catch( Throwable $error )
				{
					Logger::getInstance()->addWarning('Failed to create procedure object "'.$itemInfo['NAME'].'": '.$error->getMessage());
				}
			}

		return NULL;
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------------ get DB ------------------------------ */
	/* -------------------------------------------------------------------- */
	private static function getDB() : ?DB
	{
		if( self::$DB || self::$dbUnavailable ) return self::$DB;

		try
		{
			self::$DB = DB::getInstance();
		}
		catch( RuntimeException $exception )
		{
			Logger::getInstance()->addWarning('DB error: "'.$exception->getMessage().'"');
			self::$dbUnavailable = true;
		}

		return self::$DB;
	}
	/* -------------------------------------------------------------------- */
	/* --------------------- get procedure class name --------------------- */
	/* -------------------------------------------------------------------- */
	private static function getProcedureClassName(string $procedureName) : string
	{
		$result = str_replace('_', ' ', $procedureName);
		$result = ucwords($result);
		$result = implode('', explode(' ', $result));
		return '\\Main\\Exchange\\Procedures\\'.$result;
	}
}