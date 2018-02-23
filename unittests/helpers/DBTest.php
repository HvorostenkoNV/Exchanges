<?php
use
	Main\Helpers\Config,
	Main\Helpers\DB;

final class DBTest extends ExchangeTestCase
{
	/* -------------------------------------------------------------------- */
	/* -------------------------- is singletone --------------------------- */
	/* -------------------------------------------------------------------- */
	public function testIsSingletone() : void
	{
		self::assertTrue
		(
			$this->singletoneImplemented(DB::class),
			str_replace('#CLASS_NAME#', DB::class, $this->messages['SINGLETONE_IMPLEMENTATION_FAILED'])
		);
	}
	/* -------------------------------------------------------------------- */
	/* -------------------------- PDO available --------------------------- */
	/* -------------------------------------------------------------------- */
	public function testPDOAvailable() : void
	{
		self::assertTrue(extension_loaded('PDO'), 'PDO extension is unavailable');
	}
	/* -------------------------------------------------------------------- */
	/* -------------------- db connection params exist -------------------- */
	/* -------------------------------------------------------------------- */
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
	/* -------------------------------------------------------------------- */
	/* ------------------------ params file exist ------------------------- */
	/* -------------------------------------------------------------------- */
	public function testParamsFileExist() : string
	{
		$paramsFilePath = PARAMS_FOLDER.DS.'db.php';
		self::assertFileIsReadable($paramsFilePath);
		return $paramsFilePath;
	}
	/* -------------------------------------------------------------------- */
	/* ------------------ db connection params workable ------------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testDBConnectionParamsExist */
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
	/* -------------------------------------------------------------------- */
	/* --------- check Exception with incomplete connection params -------- */
	/* -------------------------------------------------------------------- */
	/**
	@depends testParamsFileExist
	@depends testDBConnectionParamsExist
	*/
	public function testExceptionWithIncompleteConnectionParams(string $paramsFilePath, array $connectionParams) : void
	{
		if( is_writable($paramsFilePath) )
		{
			$paramsFileContentOrigin    = file_get_contents($paramsFilePath);
			$randConnectionParam        = array_rand($connectionParams);
			$paramsFileContentChanged   = str_replace($connectionParams[$randConnectionParam], 'phpunit_testing', $paramsFileContentOrigin);

			file_put_contents($paramsFilePath, $paramsFileContentChanged);

			try
			{
				$this->resetDBInstance();
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
	/* -------------------------------------------------------------------- */
	/* --------------------- providing correct query ---------------------- */
	/* -------------------------------------------------------------------- */
	public function testProvidingCorrectQuery() : void
	{
		self::markTestIncomplete('This test has not been implemented yet');
	}
	/* -------------------------------------------------------------------- */
	/* -------------------- providing query last error -------------------- */
	/* -------------------------------------------------------------------- */
	public function testProvidingQueryLastError() : void
	{
		self::markTestIncomplete('This test has not been implemented yet');
	}
	/* -------------------------------------------------------------------- */
	/* --------------------- reset new instance of DB --------------------- */
	/* -------------------------------------------------------------------- */
	private function resetDBInstance() : void
	{
		$config         = DB::getInstance();
		$reflection     = new ReflectionClass($config);
		$instanceProp   = $reflection->getProperty('instanceArray');

		$instanceProp->setAccessible(true);
		$instanceProp->setValue([], []);
		$instanceProp->setAccessible(false);
	}
}