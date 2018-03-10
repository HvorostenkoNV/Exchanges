<?php
declare(strict_types=1);

use
	Main\Helpers\Config,
	Main\Helpers\Localization;
/** ***********************************************************************************************
 * Test Main\Helpers\Localization class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class LocalizationClassTest extends ExchangeTestCase
{
	private
		$locTestFolder  = 'unit_test',
		$locTestFile    = 'loc_test',
		$locTestParams  =
		[
			['param1', 'value1'],
			['param2', 'value2']
		];
	/** **********************************************************************
	 * test loc folder param exist
	 * @test
	 * @return  string  loc folder param value
	 ************************************************************************/
	public function locFolderParamExist() : string
	{
		$locFolderParam = Config::getInstance()->getParam('main.localizationFolder');

		self::assertNotEmpty
		(
			$locFolderParam,
			'Loc folder param is not defined'
		);

		return $locFolderParam;
	}
	/** **********************************************************************
	 * test default loc folder param exist
	 * @test
	 * @return  string  default loc folder param value
	 ************************************************************************/
	public function defaultLocFolderParamExist() : string
	{
		$defaultLangParam = Config::getInstance()->getParam('main.defaultLang');

		self::assertNotEmpty
		(
			$defaultLangParam,
			'Default loc folder param is not defined'
		);

		return $defaultLangParam;
	}
	/** **********************************************************************
	 * loc folder full test
	 * @test
	 * @depends locFolderParamExist
	 * @param   string  $locFolderParam     loc folder param value
	 * @return  string                      loc folder path
	 ************************************************************************/
	public function locFolderFullCheck(string $locFolderParam) : string
	{
		$locFolder = DOCUMENT_ROOT.DS.$locFolderParam;

		self::assertDirectoryIsReadable
		(
			$locFolder,
			$this->getMessage('NOT_READABLE', ['PATH' => $locFolder])
		);

		foreach( $this->findAllFiles($locFolder) as $file )
		{
			$filePath       = $file->getPathname();
			$fileExtension  = $file->getExtension();
			$fileContent    = include $filePath;

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
			self::assertFileIsReadable
			(
				$filePath,
				$this->getMessage('NOT_READABLE', ['PATH' => $filePath])
			);
			self::assertTrue
			(
				is_array($fileContent),
				$this->getMessage('FILE_MUST_RETURN_ARRAY', ['PATH' => $filePath])
			);
		}

		return $locFolder;
	}
	/** **********************************************************************
	 * default loc folder full test
	 * @test
	 * @depends locFolderFullCheck
	 * @depends defaultLocFolderParamExist
	 * @param   string  $locFolder              loc folder path
	 * @param   string  $defaultLangParam       default loc folder param value
	 * @return  string                          default loc folder path
	 ************************************************************************/
	public function defaultLocFolderFullCheck(string $locFolder, string $defaultLangParam) : string
	{
		$defaultLangFolder = $locFolder.DS.$defaultLangParam;

		self::assertDirectoryIsReadable
		(
			$defaultLangFolder,
			$this->getMessage('NOT_READABLE', ['PATH' => $defaultLangFolder])
		);

		return $defaultLangFolder;
	}
	/** **********************************************************************
	 * test on throwing exception while construct with incorrect params
	 * @test
	 ************************************************************************/
	public function exceptionWithIncorrectParams() : void
	{
		try
		{
			new Localization('');
			self::fail('Expect '.InvalidArgumentException::class.' exception on creating loc object with empty path argument');
		}
		catch( InvalidArgumentException $error )
		{
			self::assertTrue(true);
		}
	}
	/** **********************************************************************
	 * test on throwing exception while construct when folder not exist
	 * @test
	 * @depends locFolderFullCheck
	 * @param   string  $locFolder  loc folder path
	 ************************************************************************/
	public function exceptionWithFolderNotExist(string $locFolder) : void
	{
		$wrongLocFolder = 'test';
		while( is_dir($locFolder.DS.$wrongLocFolder) )
			$wrongLocFolder .= '1';

		try
		{
			new Localization($wrongLocFolder);
			self::fail('Expect '.DomainException::class.' exception on creating loc object with incorrect path argument');
		}
		catch( DomainException $error )
		{
			self::assertTrue(true);
		}
	}
	/** **********************************************************************
	 * test localization class can read params, added to loc folder
	 * @test
	 * @depends locFolderFullCheck
	 * @param   string  $locFolder  loc folder path
	 ************************************************************************/
	public function canReadParams(string $locFolder) : void
	{
		if( $this->createTestLocParams($locFolder) )
		{
			$localization   = new Localization($this->locTestFolder);
			$checkingParams =
			[
				$this->locTestFile.'.'.$this->locTestParams[0][0] => $this->locTestParams[0][1],
				$this->locTestFile.'.'.$this->locTestParams[1][0] => $this->locTestParams[1][1]
			];

			foreach( $checkingParams as $index => $value )
				self::assertEquals
				(
					$value, $localization->getMessage($index),
					'Created test loc message "'.$index.'" not found or not equal test value "'.$value.'"'
				);

			$this->deleteTestLoc($locFolder);
		}
		else
			self::markTestSkipped('Unable to create test loc file for testing');
	}
	/** **********************************************************************
	 * creating test loc params
	 * @param   string  $locFolder  loc folder path
	 * @return  bool
	 ************************************************************************/
	private function createTestLocParams(string $locFolder) : bool
	{
		$locTestFolderPath  = $locFolder.DS.$this->locTestFolder;
		$locTestParamFile   = $locTestFolderPath.DS.$this->locTestFile.'.php';

		if( !is_dir($locTestFolderPath) && is_writable($locFolder) )
			if( mkdir($locTestFolderPath) )
				if( !file_exists($locTestParamFile) )
				{
					$file       = fopen($locTestParamFile, 'w');
					$content    = '
					<?php return
					[
						\''.$this->locTestParams[0][0].'\' => \''.$this->locTestParams[0][1].'\',
						\''.$this->locTestParams[1][0].'\' => \''.$this->locTestParams[1][1].'\'
					];';

					fwrite($file, $content);
					fclose($file);
				}

		return file_exists($locTestParamFile);
	}
	/** **********************************************************************
	 * deleting test loc params
	 * @param   string  $locFolder  loc folder path
	 ************************************************************************/
	private function deleteTestLoc(string $locFolder) : void
	{
		$locTestFolderPath  = $locFolder.DS.$this->locTestFolder;
		$locTestParamFile   = $locTestFolderPath.DS.$this->locTestFile.'.php';

		if( is_file($locTestParamFile) )
			if( unlink($locTestParamFile) )
				if( is_dir($locTestFolderPath) )
					rmdir($locTestFolderPath);
	}
}