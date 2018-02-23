<?php
use
	PHPUnit\Framework\TestCase,
	Main\Helpers\Config,
	Main\Helpers\Localization;

final class LocalizationTest extends TestCase
{
	private
		$locTestFolder  = 'unittest',
		$locTestFile    = 'loctest',
		$locTestParams  =
		[
			['param1', 'value1'],
			['param2', 'value2']
		];
	/* -------------------------------------------------------------------- */
	/* ---------------------- loc folder param exist ---------------------- */
	/* -------------------------------------------------------------------- */
	public function testLocFolderParamExist() : string
	{
		$locFolderParam = Config::getInstance()->getParam('main.localizationFolder');
		self::assertNotEmpty($locFolderParam, 'Loc folder param is not defined');
		return $locFolderParam;
	}
	/* -------------------------------------------------------------------- */
	/* ------------------ default loc folder param exist ------------------ */
	/* -------------------------------------------------------------------- */
	public function testDefaultLocFolderParamExist() : string
	{
		$defaultLangParam = Config::getInstance()->getParam('main.defaultLang');
		self::assertNotEmpty($defaultLangParam, 'Default loc folder param is not defined');
		return $defaultLangParam;
	}
	/* -------------------------------------------------------------------- */
	/* ---------------------- loc folder full check ----------------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testLocFolderParamExist */
	public function testLocFolderFullCheck(string $locFolderParam) : string
	{
		$locFolder = DOCUMENT_ROOT.DS.$locFolderParam;
		self::assertDirectoryIsReadable($locFolder, 'Loc folder is not readable');

		foreach( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($locFolder)) as $file )
			if( $file->isFile() )
			{
				$filePath       = $file->getPathname();
				$fileContent    = include $filePath;

				self::assertEquals
				(
					'php', $file->getExtension(),
					'Not php file found in loc folder by path '.$filePath
				);
				self::assertFileIsReadable
				(
					$filePath,
					'Unreadable file found in loc folder by path '.$filePath
				);
				self::assertTrue
				(
					is_array($fileContent),
					'Found loc file, that returns non array data by path '.$filePath
				);
			}

		return $locFolder;
	}
	/* -------------------------------------------------------------------- */
	/* ------------------ default loc folder full check ------------------- */
	/* -------------------------------------------------------------------- */
	/**
	@depends testLocFolderFullCheck
	@depends testDefaultLocFolderParamExist
	*/
	public function testDefaultLocFolderFullCheck(string $locFolder, string $defaultLangParam) : string
	{
		$defaultLangFolder = $locFolder.DS.$defaultLangParam;

		self::assertDirectoryExists($defaultLangFolder, 'Default loc folder is not exist');

		return $defaultLangFolder;
	}
	/* -------------------------------------------------------------------- */
	/* -------------- throw exception with incorrect params --------------- */
	/* -------------------------------------------------------------------- */
	public function testExceptionWithIncorrectParams() : void
	{
		$this->expectException(InvalidArgumentException::class);
		new Localization('');
	}
	/* -------------------------------------------------------------------- */
	/* ------------- throw exception when folder doesnt exist ------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testLocFolderFullCheck */
	public function testExceptionWhithFolderDoesntExist(string $locFolder) : void
	{
		$unexistLocFolder = 'test';
		while( is_dir($locFolder.DS.$unexistLocFolder) )
			$unexistLocFolder .= '1';

		$this->expectException(DomainException::class);
		new Localization($unexistLocFolder);
	}
	/* -------------------------------------------------------------------- */
	/* -------------------------- can read params ------------------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testLocFolderFullCheck */
	public function testCanReadParams(string $locFolder) : void
	{
		if( $this->createTestLoc($locFolder) )
		{
			$localization   = new Localization($this->locTestFolder);
			$chackingParams =
			[
				$this->locTestFile.'.'.$this->locTestParams[0][0] => $this->locTestParams[0][1],
				$this->locTestFile.'.'.$this->locTestParams[1][0] => $this->locTestParams[1][1]
			];

			foreach( $chackingParams as $index => $value )
				self::assertTrue
				(
					$localization->getMessage($index) == $value,
					'Created test loc message "'.$index.'" not found or not equal test value "'.$value.'"'
				);

			$this->deleteTestLoc($locFolder);
		}
		else
			self::markTestSkipped('Unable to create test loc file for testing');
	}
	/* -------------------------------------------------------------------- */
	/* --------------------- create test localization --------------------- */
	/* -------------------------------------------------------------------- */
	private function createTestLoc(string $locFolder) : bool
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
	/* -------------------------------------------------------------------- */
	/* --------------------- delete test localization --------------------- */
	/* -------------------------------------------------------------------- */
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