<?php
declare(strict_types=1);

use
	PHPUnit\DbUnit\DataSet\IDataSet     as DbDataSet,
	PHPUnit\DbUnit\TestCaseTrait        as DbTestCaseTrait,
	PHPUnit\DbUnit\Database\Connection  as DbConnection;
/** ***********************************************************************************************
 * Main Exchange DB TestCase to inherit
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class DBExchangeTestCase extends ExchangeTestCase
{
	use DbTestCaseTrait;

	private
		$connection = NULL;
	private static
		$pdo        = NULL,
		$tempData   = [];
	/** **********************************************************************
	 * get temp connection
	 * @return  DbConnection
	 ************************************************************************/
	final protected function getConnection() : DbConnection
	{
		if( !$this->connection )
			$this->connection = $this->createDefaultDBConnection(self::getPDO(), $GLOBALS['DB_NAME']);
		return $this->connection;
	}
	/** **********************************************************************
	 * get data set
	 * @return  DbDataSet
	 ************************************************************************/
	final protected function getDataSet() : DbDataSet
	{
		self::$tempData = static::prepareTempData();
		return $this->createArrayDataSet(self::$tempData);
	}
	/** **********************************************************************
	 * get PDO statement
	 * @return  PDO     PDO connection statement
	 ************************************************************************/
	final protected static function getPDO() : PDO
	{
		if( !self::$pdo )
			self::$pdo = new PDO
			(
				'mysql:dbname='.$GLOBALS['DB_NAME'].';host='.$GLOBALS['DB_HOST'],
				$GLOBALS['DB_LOGIN'],
				$GLOBALS['DB_PASSWORD']
			);

		return self::$pdo;
	}
	/** **********************************************************************
	 * get temp data
	 * @return  array   temp data
	 * @example
			[
				$tableName =>
				[
					[ id => 1, name => Test1 ],
					[ id => 2, name => Test2 ]
				]
			]
	 ************************************************************************/
	final protected static function getTempData() : array
	{
		if( count(self::$tempData) <= 0 )
			self::$tempData = self::prepareTempData();
		return self::$tempData;
	}
	/** **********************************************************************
	 * prepare temp data for testing
	 * @return  array   temp data
	 ************************************************************************/
	abstract protected static function prepareTempData() : array;
}