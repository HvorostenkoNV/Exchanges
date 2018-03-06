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
		$tempData   = [],
		$nativeData = [];
	/** **********************************************************************
	 * construct
	 ************************************************************************/
	public static function setUpBeforeClass() : void
	{
		self::$tempData     = static::prepareDBTempData();
		self::$nativeData   = static::prepareDBCurrentData(array_keys(self::$tempData));
	}
	/** **********************************************************************
	 * destruct
	 ************************************************************************/
	public static function tearDownAfterClass() : void
	{
		static::removeDBTempData();
	}
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
		$data       = [];
		$workTables = array_merge(array_keys(self::$nativeData), array_keys(self::$tempData));

		if( count($workTables) > 0 )
			foreach( $workTables as $tableName)
			{
				$nativeData = array_key_exists($tableName, self::$nativeData)   ? self::$nativeData[$tableName] : [];
				$tempData   = array_key_exists($tableName, self::$tempData)     ? self::$tempData[$tableName]   : [];
				$data[$tableName] = array_merge($nativeData, $tempData);
			}

		return $this->createArrayDataSet($data);
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
	 * get db temp data for testing
	 * @return  array   seted temp data
	 ************************************************************************/
	final protected static function getTempData() : array
	{
		return self::$tempData;
	}
	/** **********************************************************************
	 * prepare db current data for testing
	 * @param   string[]    $workTables array of tables names to query current data
	 * @return  array   current data
	 ************************************************************************/
	private static function prepareDBCurrentData(array $workTables = []) : array
	{
		$data = [];

		if( count($workTables) > 0 )
			foreach( $workTables as $tableName )
			{
				$sqlQuery = 'SELECT * FROM #TABLE_NAME#';
				$sqlQuery = str_replace('#TABLE_NAME#', $tableName, $sqlQuery);

				try
				{
					$queryResult = self::getPDO()->query($sqlQuery)->fetchAll(PDO::FETCH_ASSOC);
					if( count($queryResult) > 0 ) $data[$tableName] = $queryResult;
				}
				catch( Throwable $error )
				{

				}
			}

		return $data;
	}
	/** **********************************************************************
	 * remove db temp data for testing
	 ************************************************************************/
	private static function removeDBTempData() : void
	{
		if( count(self::$tempData) > 0 )
			foreach( self::$tempData as $tableName => $itemsArray )
				foreach( $itemsArray as $itemInfo )
				{
					$sqlQuery       = 'DELETE FROM #TABLE_NAME# WHERE #CONDITION#';
					$sqlCondition   = [];
					$queryValues    = [];

					foreach( $itemInfo as $index => $value )
					{
						$sqlCondition[] = $index.' = ?';
						$queryValues[]  = $value;
					}

					$sqlQuery = str_replace('#TABLE_NAME#', $tableName,                              $sqlQuery);
					$sqlQuery = str_replace('#CONDITION#',  implode(' AND ', $sqlCondition),    $sqlQuery);

					self::getPDO()->prepare($sqlQuery)->execute($queryValues);
				}
	}
	/** **********************************************************************
	 * prepare temp data for testing
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
	abstract protected static function prepareDBTempData() : array;
}