<?php
use
	PHPUnit\Framework\Error\Error as FatalError,
	Main\Helpers\Logger;

final class LoggerTest extends ExchangeTestCase
{
	/* -------------------------------------------------------------------- */
	/* -------------------------- is singletone --------------------------- */
	/* -------------------------------------------------------------------- */
	public function testIsSingletone() : void
	{
		self::assertTrue
		(
			$this->singletoneImplemented(Logger::class),
			str_replace('#CLASS_NAME#', Logger::class, $this->messages['SINGLETONE_IMPLEMENTATION_FAILED'])
		);
	}
	/* -------------------------------------------------------------------- */
	/* -------------------- logs folder constant exist -------------------- */
	/* -------------------------------------------------------------------- */
	public function testLogsFolderConstantExist() : string
	{
		self::assertTrue(defined('LOGS_FOLDER'), 'Logs constant is not defined');
		self::assertNotEquals($this->documentRoot, LOGS_FOLDER, 'Logs constant equals document root');
		return LOGS_FOLDER;
	}
	/* -------------------------------------------------------------------- */
	/* ---------------------- logs folder full check ---------------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testLogsFolderConstantExist */
	public function testLogsFolderFullCheck(string $logsConstantValue) : string
	{
		self::assertDirectoryIsReadable($logsConstantValue, 'Logs folder is not readable');
		self::assertDirectoryIsWritable($logsConstantValue, 'Logs folder is not writable');

		foreach( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($logsConstantValue)) as $file )
			if( $file->isFile() )
				self::assertEquals
				(
					'txt', $file->getExtension(),
					'Not txt file found in logs folder by path '.$file->getPathname()
				);

		return $logsConstantValue;
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------ log file creating ------------------------- */
	/* -------------------------------------------------------------------- */
	/**
	@depends testLogsFolderFullCheck
	@depends testIsSingletone
	*/
	public function testLogFileCreating(string $logsFolder) : void
	{
		$loger          = Logger::getInstance();
		$logsCountStart = 0;
		$logsCountEnd   = 0;

		foreach( scandir($logsFolder) as $file )
			if( is_file($logsFolder.DS.$file) )
				$logsCountStart++;

		$loger->addNotice('Unit testing');
		$loger->write();

		foreach( scandir($logsFolder) as $file )
			if( is_file($logsFolder.DS.$file) )
				$logsCountEnd++;

		self::assertEquals($logsCountStart + 1, $logsCountEnd, 'Expected new created log file not found');
	}
	/* -------------------------------------------------------------------- */
	/* ----------------- seted messages exists in log file ---------------- */
	/* -------------------------------------------------------------------- */
	/**
	@depends testLogsFolderFullCheck
	@depends testIsSingletone
	*/
	public function testSetedMessagesExistsInLogFile(string $logsFolder) : void
	{
		$loger              = Logger::getInstance();
		$testMessageNotice  = 'This is test notice';
		$testMessageWarning = 'This is test warning';
		$logContent         = '';

		$loger->addNotice($testMessageNotice);
		$loger->addWarning($testMessageWarning);
		$loger->write();

		foreach( scandir($logsFolder, SCANDIR_SORT_DESCENDING) as $file )
		{
			$logContent = file_get_contents($logsFolder.DS.$file);
			break;
		}

		$this->assertContains($testMessageNotice,   $logContent, 'Failed to find log with seted mesages');
		$this->assertContains($testMessageWarning,  $logContent, 'Failed to find log with seted mesages');
	}
	/* -------------------------------------------------------------------- */
	/* ------------------- seting error stops executing ------------------- */
	/* -------------------------------------------------------------------- */
	public function testSetingErrorStopsExecuting() : void
	{
		try
		{
			Logger::getInstance()->addError('UNIT TEST');
			self::fail('No crush on setting error');
		}
		catch( FatalError $error )
		{
			self::assertTrue(true);
		}
	}
}