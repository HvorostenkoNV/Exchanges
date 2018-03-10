<?php
declare(strict_types=1);

use
	Main\Helpers\Config,
	Main\Helpers\DB;
/** ***********************************************************************************************
 * Test Main\Helpers\DB class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBClassTest extends DBExchangeTestCase
{
	private static $testTableSchema =
	[
		'name'      => 'phpunit_test_table',
		'columns'   =>
		[
			'item_id'   => 'ID',
			'item_name' => 'NAME',
			'item_code' => 'CODE'
		]
	];
	/** **********************************************************************
	 * construct, create test db table
	 ************************************************************************/
	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();

		$tableName      = self::$testTableSchema['name'];
		$tableColumns   = self::$testTableSchema['columns'];
		$sqlQuery       =
		'
			CREATE TABLE #TABLE_NAME#
			(
				#ID# 	INT 			AUTO_INCREMENT,
				#NAME#	VARCHAR(255),
				#CODE#	VARCHAR(255),
				PRIMARY KEY (#ID#)
			)
		';

		$sqlQuery = str_replace('#TABLE_NAME#', $tableName,                 $sqlQuery);
		$sqlQuery = str_replace('#ID#',         $tableColumns['item_id'],   $sqlQuery);
		$sqlQuery = str_replace('#NAME#',       $tableColumns['item_name'], $sqlQuery);
		$sqlQuery = str_replace('#CODE#',       $tableColumns['item_code'], $sqlQuery);

		self::getPDO()->query($sqlQuery);
	}
	/** **********************************************************************
	 * destruct, drop test db table
	 ************************************************************************/
	public static function tearDownAfterClass() : void
	{
		parent::tearDownAfterClass();

		$sqlQuery = 'DROP TABLE #TABLE_NAME#';
		$sqlQuery = str_replace('#TABLE_NAME#', self::$testTableSchema['name'], $sqlQuery);

		self::getPDO()->query($sqlQuery);
	}
	/** **********************************************************************
	 * DB is singleton
	 * @test
	 ************************************************************************/
	public function isSingleton() : void
	{
		self::assertTrue
		(
			$this->singletonImplemented(DB::class),
			$this->getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => DB::class])
		);
	}
	/** **********************************************************************
	 * test PDO extension available
	 * @test
	 ************************************************************************/
	public function pdoAvailable() : void
	{
		self::assertTrue
		(
			extension_loaded('PDO'),
			'PDO extension is unavailable'
		);
	}
	/** **********************************************************************
	 * test params file exist
	 * @test
	 * @return  string  params file path
	 ************************************************************************/
	public function paramsFileExist() : string
	{
		$paramsFilePath = PARAMS_FOLDER.DS.'db.php';
		self::assertFileIsReadable
		(
			$paramsFilePath,
			$this->getMessage('NOT_READABLE', ['PATH' => $paramsFilePath])
		);
		return $paramsFilePath;
	}
	/** **********************************************************************
	 * test db connection params exist
	 * @test
	 * @return  array   connection params array
	 ************************************************************************/
	public function dbConnectionParamsExist() : array
	{
		$config     = Config::getInstance();
		$dbName     = $config->getParam('db.name');
		$dbLogin    = $config->getParam('db.login');
		$dbPassword = $config->getParam('db.password');
		$dbHost     = $config->getParam('db.host');

		self::assertNotEmpty($dbName,       'DB name param no exist');
		self::assertNotEmpty($dbLogin,      'DB login param no exist');
		self::assertNotEmpty($dbPassword,   'DB password param no exist');
		self::assertNotEmpty($dbHost,       'DB host param no exist');

		return
		[
			'name'      => $dbName,
			'login'     => $dbLogin,
			'password'  => $dbPassword,
			'host'      => $dbHost
		];
	}
	/** **********************************************************************
	 * test db connection params workable
	 * @test
	 * @depends dbConnectionParamsExist
	 * @param   array   $connectionParams   connection params
	 ************************************************************************/
	public function dbConnectionParamsWorkable(array $connectionParams) : void
	{
		try
		{
			mysqli_connect
			(
				$connectionParams['host'],
				$connectionParams['login'],
				$connectionParams['password'],
				$connectionParams['name']
			);
			self::assertTrue(true);
		}
		catch( Throwable $error )
		{
			self::fail('DB connection failed: '.$error->getMessage());
		}
	}
	/** **********************************************************************
	 * check Exception with incomplete connection params
	 * @test
	 * @depends paramsFileExist
	 * @depends dbConnectionParamsExist
	 * @depends isSingleton
	 * @param   string  $paramsFilePath     params file path
	 * @param   array   $connectionParams   connection params
	 ************************************************************************/
	public function exceptionOnIncompleteConnParams(string $paramsFilePath, array $connectionParams) : void
	{
		if( is_writable($paramsFilePath) )
		{
			$paramsFileContentOrigin    = file_get_contents($paramsFilePath);
			$randConnectionParam        = array_rand($connectionParams);
			$paramsFileContentChanged   = str_replace($connectionParams[$randConnectionParam], 'phpunit_testing', $paramsFileContentOrigin);

			file_put_contents($paramsFilePath, $paramsFileContentChanged);

			try
			{
				$this->resetSingletonInstance(DB::class);
				DB::getInstance();
				self::fail('Caught no exception with incomplete DB connection params');
			}
			catch( RuntimeException $error )
			{
				self::assertTrue(true);
			}

			file_put_contents($paramsFilePath, $paramsFileContentOrigin);
		}
		else
			self::markTestSkipped('Unable to rewrite db params file for testing');
	}
	/** **********************************************************************
	 * test providing correct query
	 * @test
	 * @depends isSingleton
	 * @depends pdoAvailable
	 * @depends dbConnectionParamsWorkable
	 ************************************************************************/
	public function providingCorrectQuery() : void
	{
		$pdo                = self::getPDO();
		$db                 = DB::getInstance();
		$tableName          = self::$testTableSchema['name'];
		$columns            = self::$testTableSchema['columns'];
		$tempData           = self::getTempData()[$tableName];
		$demoDataRandomItem = $tempData[array_rand($tempData)];
		$sqlQueries         =
		[
			'SELECT * 			FROM #TABLE_NAME#',
			'SELECT #COLUMNS# 	FROM #TABLE_NAME#',
			'SELECT *			FROM #TABLE_NAME#	WHERE 		#ID_COLUMN# = ? AND #CODE_COLUMN# = ?',
			'SELECT #COLUMNS#	FROM #TABLE_NAME# 	WHERE 		#ID_COLUMN#		IN (?, ?)',
			'SELECT *			FROM #TABLE_NAME# 	ORDER BY	#NAME_COLUMN#	ASC',
			'SELECT *			FROM #TABLE_NAME# 	ORDER BY	#CODE_COLUMN#	DESC'
		];
		$sqlQueriesValues   =
		[
			2   => [$demoDataRandomItem[$columns['item_id']], $demoDataRandomItem[$columns['item_code']]],
			3   => [2, rand(3, count($tempData))]
		];

		foreach( $sqlQueries as $index => $string )
		{
			$string = str_replace('#TABLE_NAME#',   self::$testTableSchema['name'],                                 $string);
			$string = str_replace('#COLUMNS#',      implode(', ', [$columns['item_id'], $columns['item_name']]),    $string);
			$string = str_replace('#ID_COLUMN#',    $columns['item_id'],                                            $string);
			$string = str_replace('#NAME_COLUMN#',  $columns['item_name'],                                          $string);
			$string = str_replace('#CODE_COLUMN#',  $columns['item_code'],                                          $string);
			$string = str_replace("\t",             ' ',                                                            $string);
			$string = preg_replace('/\s+/', ' ', trim($string));

			$sqlQueries[$index] = $string;
		}

		foreach( $sqlQueries as $index => $sqlString )
		{
			$queryValues    = array_key_exists($index, $sqlQueriesValues) ? $sqlQueriesValues[$index] : [];
			$queryNative    = [];
			$queryByDBClass = [];

			$statement = $pdo->prepare($sqlString);
			$statement->execute($queryValues);
			foreach( $statement->fetchAll(PDO::FETCH_ASSOC) as $itemInfo )
				$queryNative[] = $itemInfo;

			$dbClassQuery = $db->query($sqlString, $queryValues);
			while( !$dbClassQuery->isEmpty() )
			{
				$data       = $dbClassQuery->pop();
				$dataArray  = [];

				foreach( $data->getKeys() as $key )
					$dataArray[$key] = $data->get($key);

				$queryByDBClass[] = $dataArray;
			}

			self::assertEquals
			(
				$queryNative, $queryByDBClass,
				'Different query results on "'.$sqlString.'"'
			);
		}
	}
	/** **********************************************************************
	 * test providing query last error
	 * @test
	 * @depends providingCorrectQuery
	 ************************************************************************/
	public function providingQueryLastError() : void
	{
		$db = DB::getInstance();
		$db->query('Some incorrect sql query string');

		self::assertTrue
		(
			$db->hasLastError() && strlen($db->getLastError()) > 0,
			'No error provided with incorrect query'
		);
	}
	/** **********************************************************************
	 * test saving
	 * @test
	 * @depends providingCorrectQuery
	 * @return  int     created test item id
	 ************************************************************************/
	public function providingCorrectSaving() : int
	{
		$db                 = DB::getInstance();
		$lastTestItemId     = 0;
		$newTestItemId      = 0;
		$columns            = self::$testTableSchema['columns'];
		$sqlGetLastItem     = 'SELECT #ID_COLUMN# FROM #TABLE_NAME# ORDER BY #ID_COLUMN# DESC LIMIT 1';
		$sqlCreateNewItem   = 'insert INTO #TABLE_NAME# (#NAME_COLUMN#, #CODE_COLUMN#) VALUES (?, ?)';

		$sqlGetLastItem     = str_replace('#ID_COLUMN#',    $columns['item_id'],            $sqlGetLastItem);
		$sqlGetLastItem     = str_replace('#TABLE_NAME#',   self::$testTableSchema['name'], $sqlGetLastItem);

		$sqlCreateNewItem   = str_replace('#TABLE_NAME#',   self::$testTableSchema['name'], $sqlCreateNewItem);
		$sqlCreateNewItem   = str_replace('#NAME_COLUMN#',  $columns['item_name'],          $sqlCreateNewItem);
		$sqlCreateNewItem   = str_replace('#CODE_COLUMN#',  $columns['item_code'],          $sqlCreateNewItem);

		foreach( self::getPDO()->query($sqlGetLastItem)->fetchAll(PDO::FETCH_ASSOC) as $itemInfo )
			$lastTestItemId = intval($itemInfo[self::$testTableSchema['columns']['item_id']]);

		if( $lastTestItemId > 0 )
			$newTestItemId = $db->save($sqlCreateNewItem, ['Test name', 'Test code']);

		if( $newTestItemId <= 0 )
			self::fail('Saving process failed. Error: '.$db->getLastError());
		else
			self::assertEquals
			(
				$lastTestItemId + 1, $newTestItemId,
				'New created item has unexpected id. Error: '.$db->getLastError()
			);

		return $newTestItemId;
	}
	/** **********************************************************************
	 * test deleting
	 * @test
	 * @param   int $itemId     created test item
	 * @depends providingCorrectSaving
	 ************************************************************************/
	public function providingCorrectDeleting(int $itemId) : void
	{
		$pdo                = self::getPDO();
		$db                 = DB::getInstance();
		$columns            = self::$testTableSchema['columns'];
		$sqlDropNewItem     = 'DELETE FROM #TABLE_NAME# WHERE #ID_COLUMN# = ?';
		$sqlGetDeletedItem  = 'SELECT #ID_COLUMN# FROM #TABLE_NAME# WHERE #ID_COLUMN# = ?';

		$sqlDropNewItem     = str_replace('#TABLE_NAME#',   self::$testTableSchema['name'], $sqlDropNewItem);
		$sqlDropNewItem     = str_replace('#ID_COLUMN#',    $columns['item_id'],            $sqlDropNewItem);

		$sqlGetDeletedItem  = str_replace('#TABLE_NAME#',   self::$testTableSchema['name'], $sqlGetDeletedItem);
		$sqlGetDeletedItem  = str_replace('#ID_COLUMN#',    $columns['item_id'],            $sqlGetDeletedItem);

		self::assertTrue
		(
			$db->delete($sqlDropNewItem, [$itemId]),
			'Delete process failed. Error: '.$db->getLastError()
		);

		$statement = $pdo->prepare($sqlGetDeletedItem);
		$statement->execute([$itemId]);
		foreach( $statement->fetchAll(PDO::FETCH_ASSOC) as $itemInfo )
			self::assertNotEquals
			(
				$itemId, $itemInfo[$columns['item_id']],
				'Item was not deleted'
			);
	}
	/** **********************************************************************
	 * get temp data set
	 * @return  array   temp data
	 ************************************************************************/
	protected static function prepareDBTempData() : array
	{
		$data           = [];
		$tableName      = self::$testTableSchema['name'];
		$tableColumns   = self::$testTableSchema['columns'];

		for( $index = 1; $index <= 10; $index++ )
			$data[] =
			[
				$tableColumns['item_id']    => $index,
				$tableColumns['item_name']  => str_replace('#INDEX#', $index, 'Item name #INDEX#'),
				$tableColumns['item_code']  => str_replace('#INDEX#', $index, 'item_code_#INDEX#')
			];

		return [$tableName => $data];
	}
}