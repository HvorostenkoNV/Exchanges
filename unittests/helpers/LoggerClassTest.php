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
final class LoggerTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * Logger is singleton
	 ************************************************************************/
	public function testIsSingleton() : void
	{
		self::assertTrue
		(
			$this->singletonImplemented(Logger::class),
			$this->getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Logger::class])
		);
	}
	/** **********************************************************************
	 * test logs folder constant exist
	 * @return  string  logs folder constant value
	 ************************************************************************/
	public function testLogsFolderConstantExist() : string
	{
		self::assertTrue
		(
			defined('LOGS_FOLDER'),
			$this->getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'LOGS_FOLDER'])
		);
		self::assertNotEquals
		(
			$_SERVER['APPLICATION_ROOT'], LOGS_FOLDER,
			'Logs constant equals document root'
		);
		return LOGS_FOLDER;
	}
	/** **********************************************************************
	 * logs folder full test
	 * @param   string  $logsConstantValue      logs folder constant value
	 * @depends testLogsFolderConstantExist
	 * @return  string                          logs folder path
	 ************************************************************************/
	public function testLogsFolderFullCheck(string $logsConstantValue) : string
	{
		self::assertDirectoryIsReadable
		(
			$logsConstantValue,
			$this->getMessage('NOT_READABLE', ['PATH' => $logsConstantValue])
		);
		self::assertDirectoryIsWritable
		(
			$logsConstantValue,
			$this->getMessage('NOT_WRITABLE', ['PATH' => $logsConstantValue])
		);

		foreach( $this->findAllFiles($logsConstantValue) as $file )
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

		return $logsConstantValue;
	}
	/** **********************************************************************
	 * test if new log file creates on call need method
	 * @param   string  $logsFolder     logs folder path
	 * @depends testLogsFolderFullCheck
	 * @depends testIsSingleton
	 ************************************************************************/
	public function testLogFileCreating(string $logsFolder) : void
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
	 * @param   string  $logsFolder     logs folder path
	 * @depends testLogsFolderFullCheck
	 * @depends testIsSingleton
	 ************************************************************************/
	public function testSetedMessagesExistsInLogFile(string $logsFolder) : void
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
			self::assertContains($message, $logContent, 'Failed to find log file with seted test message');
	}
	/** **********************************************************************
	 * test system shut down on seting ERROR
	 * @depends testIsSingleton
	 ************************************************************************/
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