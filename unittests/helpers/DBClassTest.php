<?php
declare(strict_types=1);

use
	PHPUnit\DbUnit\TestCaseTrait as DbTestCaseTrait,
	Main\Helpers\Config,
	Main\Helpers\DB;
/** ***********************************************************************************************
 * Test Main\Helpers\DB class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBClassTest extends ExchangeTestCase
{
	use DbTestCaseTrait;
	/** **********************************************************************
	 * get temp connection
	 * @return  PHPUnit\DbUnit\Database\Connection
	 ************************************************************************/
	protected function getConnection()
	{
		$pdo = new PDO
		(
			'mysql:dbname='.$GLOBALS['DB_DBNAME'].';host='.$GLOBALS['DB_HOST'],
			$GLOBALS['DB_LOGIN'],
			$GLOBALS['DB_PASSWORD']
		);

		return $this->createDefaultDBConnection($pdo, ':memory:');
	}
	/** **********************************************************************
	 * get data set
	 * @return  PHPUnit\DbUnit\DataSet\IDataSet
	 ************************************************************************/
	protected function getDataSet()
	{
		return $this->createFlatXMLDataSet(__DIR__.'/dbTestSeeding.xml');
	}
	/** **********************************************************************
	 * DB is singleton
	 ************************************************************************/
	public function testIsSingleton() : void
	{
		self::assertTrue
		(
			$this->singletonImplemented(DB::class),
			$this->getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => DB::class])
		);
	}
	/** **********************************************************************
	 * test PDO extension available
	 ************************************************************************/
	public function testPDOAvailable() : void
	{
		self::assertTrue(extension_loaded('PDO'), 'PDO extension is unavailable');
	}
	/** **********************************************************************
	 * test params file exist
	 * @return  string  params file path
	 ************************************************************************/
	public function testParamsFileExist() : string
	{
		$paramsFilePath = PARAMS_FOLDER.DS.'db.php';
		self::assertFileIsReadable($paramsFilePath);
		return $paramsFilePath;
	}
	/** **********************************************************************
	 * test db connection params exist
	 * @return  array   connection params array
	 ************************************************************************/
	public function testDBConnectionParamsExist() : array
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
	 * test db connection params exist
	 * @param   array   $connectionParams   connection params
	 * @depends testDBConnectionParamsExist
	 ************************************************************************/
	public function testDBConnectionParamsWorkable(array $connectionParams) : void
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
	 * @param   string  $paramsFilePath     params file path
	 * @param   array   $connectionParams   connection params
	 * @depends testParamsFileExist
	 * @depends testDBConnectionParamsExist
	 ************************************************************************/
	public function testExceptionOnIncompleteConnParams(string $paramsFilePath, array $connectionParams) : void
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
	 * TODO
	 ************************************************************************/
	public function testProvidingCorrectQuery() : void
	{
		self::markTestIncomplete('This test has not been implemented yet');
	}
	/** **********************************************************************
	 * test providing query last error
	 * TODO
	 ************************************************************************/
	public function testProvidingQueryLastError() : void
	{
		self::markTestIncomplete('This test has not been implemented yet');
	}
	/** **********************************************************************
	 * test providing query result structure
	 * TODO
	 ************************************************************************/
	public function testProvidingQueryResultStructure() : void
	{
		self::markTestIncomplete('This test has not been implemented yet');
	}
	/** **********************************************************************
	 * test saving
	 * TODO
	 ************************************************************************/
	public function testSave() : void
	{
		self::markTestIncomplete('This test has not been implemented yet');
	}
	/** **********************************************************************
	 * test deleting
	 * TODO
	 ************************************************************************/
	public function testDelete() : void
	{
		self::markTestIncomplete('This test has not been implemented yet');
	}
}