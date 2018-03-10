<?php
declare(strict_types=1);

use
	PHPUnit\Framework\Error\Error as FatalError,
	Main\Helpers\Logger;
/** ***********************************************************************************************
 * Test Main\Helpers\Logger class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class LoggerClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * Logger is singleton
	 * @test
	 ************************************************************************/
	public function isSingleton() : void
	{
		self::assertTrue
		(
			$this->singletonImplemented(Logger::class),
			$this->getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Logger::class])
		);
	}
	/** **********************************************************************
	 * logs folder full test
	 * @test
	 * @return  string                      logs folder path
	 ************************************************************************/
	public function logsFolderFullCheck() : string
	{
		self::assertDirectoryIsReadable
		(
			LOGS_FOLDER,
			$this->getMessage('NOT_READABLE', ['PATH' => LOGS_FOLDER])
		);
		self::assertDirectoryIsWritable
		(
			LOGS_FOLDER,
			$this->getMessage('NOT_WRITABLE', ['PATH' => LOGS_FOLDER])
		);

		foreach( $this->findAllFiles(LOGS_FOLDER) as $file )
		{
			$filePath       = $file->getPathname();
			$fileExtension  = $file->getExtension();

			self::assertEquals
			(
				'txt', $fileExtension,
				$this->getMessage('WRONG_EXTENSION',
				[
					'PATH'      => $filePath,
					'NEED'      => 'txt',
					'CURRENT'   => $fileExtension
				])
			);
		}

		return LOGS_FOLDER;
	}
	/** **********************************************************************
	 * test if new log file creates on call need method
	 * @test
	 * @depends logsFolderFullCheck
	 * @depends isSingleton
	 * @param   string  $logsFolder     logs folder path
	 ************************************************************************/
	public function logFileCreating(string $logsFolder) : void
	{
		$logger         = Logger::getInstance();
		$logsCountStart = count($this->findAllFiles($logsFolder));

		$logger->addNotice('Unit testing');
		$logger->write();

		self::assertEquals
		(
			$logsCountStart + 1,
			count($this->findAllFiles($logsFolder)),
			'Expected new created log file not found'
		);
	}
	/** **********************************************************************
	 * test if new log file contains messages, that was seted
	 * @test
	 * @depends logsFolderFullCheck
	 * @depends isSingleton
	 * @param   string  $logsFolder     logs folder path
	 ************************************************************************/
	public function setedMessagesExistsInLogFile(string $logsFolder) : void
	{
		$logger             = Logger::getInstance();
		$testMessageNotice  = 'This is test notice';
		$testMessageWarning = 'This is test warning';
		$logContent         = '';

		$logger->addNotice($testMessageNotice);
		$logger->addWarning($testMessageWarning);
		$logger->write();

		foreach( scandir($logsFolder, SCANDIR_SORT_DESCENDING) as $file )
		{
			$logContent = file_get_contents($logsFolder.DS.$file);
			break;
		}

		foreach( [$testMessageNotice, $testMessageWarning] as $message )
			self::assertContains
			(
				$message, $logContent,
				'Failed to find log file with seted test message'
			);
	}
	/** **********************************************************************
	 * test system shut down on seting ERROR
	 * @test
	 * @depends isSingleton
	 ************************************************************************/
	public function setingErrorStopsExecuting() : void
	{
		try
		{
			Logger::getInstance()->addError('UNIT TEST');
			self::fail('Expect shut down on call "addError" method');
		}
		catch( FatalError $error )
		{
			self::assertTrue(true);
		}
	}
}