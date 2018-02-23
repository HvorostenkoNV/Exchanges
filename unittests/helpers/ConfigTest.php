<?php
use
	PHPUnit\Framework\Error\Error as FatalError,
	Main\Helpers\Config;

final class ConfigTest extends ExchangeTestCase
{
	private
		$paramsFolderPermissions    = '540',
		$paramsFilesPermissions     = '440',
		$paramTestFolder            = 'unittest',
		$paramTestFile              = 'paramstest',
		$testParams                 =
		[
			['param1', 'value1'],
			['param2', 'value2']
		];
	/* -------------------------------------------------------------------- */
	/* -------------------------- is singletone --------------------------- */
	/* -------------------------------------------------------------------- */
	public function testIsSingletone() : void
	{
		self::assertTrue
		(
			$this->singletoneImplemented(Config::class),
			$this->getMessage('SINGLETONE_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Config::class])
		);
	}
	/* -------------------------------------------------------------------- */
	/* ------------------- params folder constant exist ------------------- */
	/* -------------------------------------------------------------------- */
	public function testParamsFolderConstantExist() : string
	{
		self::assertTrue
		(
			defined('PARAMS_FOLDER'),
			$this->getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'PARAMS_FOLDER'])
		);
		self::assertNotEquals
		(
			$this->documentRoot, PARAMS_FOLDER,
			'Params constant equals document root'
		);
		return PARAMS_FOLDER;
	}
	/* -------------------------------------------------------------------- */
	/* --------------------- params folder full check --------------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testParamsFolderConstantExist */
	public function testParamsFolderFullCheck(string $paramsConstantValue) : string
	{
		$dirPermissions = $this->getPermissions($paramsConstantValue);

		self::assertEquals
		(
			$this->paramsFolderPermissions,
			$dirPermissions,
			$this->getMessage('WRONG_PERMISSIONS',
			[
				'PATH'      => $paramsConstantValue,
				'NEED'      => $this->paramsFolderPermissions,
				'CURRENT'   => $dirPermissions
			])
		);

		foreach( $this->findAllFiles($paramsConstantValue) as $file )
		{
			$filePath           = $file->getPathname();
			$filePermissions    = $this->getPermissions($filePath);
			$fileExtension      = $file->getExtension();
			$fileContent        = include $filePath;

			self::assertEquals
			(
				$this->paramsFilesPermissions, $filePermissions,
				$this->getMessage('WRONG_PERMISSIONS',
				[
					'PATH'      => $filePath,
					'NEED'      => $this->paramsFilesPermissions,
					'CURRENT'   => $filePermissions
				])
			);
			self::assertEquals
			(
				'php', $fileExtension,
				$this->getMessage('WRONG_EXTENSION',
				[
					'PATH'      => $filePath,
					'NEED'      => 'php',
					'CURRENT'   => $fileExtension
				])
			);
			self::assertTrue
			(
				is_array($fileContent),
				$this->getMessage('FILE_MUST_RETURN_ARRAY', ['PATH' => $filePath])
			);
		}

		return $paramsConstantValue;
	}
	/* -------------------------------------------------------------------- */
	/* -------------------------- can read params ------------------------- */
	/* -------------------------------------------------------------------- */
	/**
	@depends testParamsFolderFullCheck
	@depends testIsSingletone
	*/
	public function testCanReadParams(string $paramsPath) : void
	{
		if( $this->createTestParams($paramsPath) )
		{
			$this->resetSingletoneInstance(Config::class);

			$checkingParams =
			[
				$this->paramTestFolder.'.'.$this->paramTestFile.'.'.$this->testParams[0][0] => $this->testParams[0][1],
				$this->paramTestFolder.'.'.$this->paramTestFile.'.'.$this->testParams[1][0] => $this->testParams[1][1]
			];

			foreach( $checkingParams as $index => $value )
				self::assertTrue
				(
					Config::getInstance()->getParam($index) == $value,
					'Created test param "'.$index.'" not found or not equal test value "'.$value.'"'
				);

			$this->deleteTestParams($paramsPath);
		}
		else
			self::markTestSkipped('Unable to create test param file for testing');
	}
	/* -------------------------------------------------------------------- */
	/* ------------------- crushed without params folder ------------------ */
	/* -------------------------------------------------------------------- */
	/**
	@depends testParamsFolderFullCheck
	@depends testIsSingletone
	*/
	public function testCrushedWithoutParamsFolder(string $paramsPath) : void
	{
		$paramsOriginPath   = $paramsPath;
		$paramsTestPath     = $paramsPath.'_phpunit_testing';

		if( rename($paramsOriginPath, $paramsTestPath) )
		{
			$this->resetConfigInstance();

			try
			{
				Config::getInstance();
				self::fail('No crush without params folder');
			}
			catch( FatalError $error )
			{
				self::assertTrue(true);
			}

			rename($paramsTestPath, $paramsOriginPath);
		}
		else
			self::markTestSkipped('Unable to rename param folder for testing');
	}
	/* -------------------------------------------------------------------- */
	/* ------------------- reset new instance of config ------------------- */
	/* -------------------------------------------------------------------- */
	private function resetConfigInstance() : void
	{
		$config         = Config::getInstance();
		$reflection     = new ReflectionClass($config);
		$instanceProp   = $reflection->getProperty('instanceArray');

		$instanceProp->setAccessible(true);
		$instanceProp->setValue([], []);
		$instanceProp->setAccessible(false);
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------ create test params ------------------------ */
	/* -------------------------------------------------------------------- */
	private function createTestParams(string $paramsPath) : bool
	{
		$paramsTestFolderPath   = $paramsPath.DS.$this->paramTestFolder;
		$paramsTestParamFile    = $paramsTestFolderPath.DS.$this->paramTestFile.'.php';

		if( !is_dir($paramsTestFolderPath) && is_writable($paramsPath) )
			if( mkdir($paramsTestFolderPath) )
				if( !file_exists($paramsTestParamFile) )
				{
					$file       = fopen($paramsTestParamFile, 'w');
					$content    = '
					<?php return
					[
						\''.$this->testParams[0][0].'\' => \''.$this->testParams[0][1].'\',
						\''.$this->testParams[1][0].'\' => \''.$this->testParams[1][1].'\'
					];';

					fwrite($file, $content);
					fclose($file);
				}

		return file_exists($paramsTestParamFile);
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------ delete test params ------------------------ */
	/* -------------------------------------------------------------------- */
	private function deleteTestParams(string $paramsPath) : void
	{
		$paramsTestFolderPath   = $paramsPath.DS.$this->paramTestFolder;
		$paramsTestParamFile    = $paramsTestFolderPath.DS.$this->paramTestFile.'.php';

		if( is_file($paramsTestParamFile) )
			if( unlink($paramsTestParamFile) )
				if( is_dir($paramsTestFolderPath) )
					rmdir($paramsTestFolderPath);
	}
}