<?php
declare(strict_types=1);

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
			$this->getMessage('SINGLETONE_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Logger::class])
		);
	}
	/* -------------------------------------------------------------------- */
	/* -------------------- logs folder constant exist -------------------- */
	/* -------------------------------------------------------------------- */
	public function testLogsFolderConstantExist() : string
	{
		self::assertTrue
		(
			defined('LOGS_FOLDER'),
			$this->getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'LOGS_FOLDER'])
		);
		self::assertNotEquals
		(
			$this->documentRoot, LOGS_FOLDER,
			'Logs constant equals document root'
		);
		return LOGS_FOLDER;
	}
	/* -------------------------------------------------------------------- */
	/* ---------------------- logs folder full check ---------------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testLogsFolderConstantExist */
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
	/* -------------------------------------------------------------------- */
	/* ------------------------ log file creating ------------------------- */
	/* -------------------------------------------------------------------- */
	/**
	@depends testLogsFolderFullCheck
	@depends testIsSingletone
	*/
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
	/* -------------------------------------------------------------------- */
	/* ----------------- seted messages exists in log file ---------------- */
	/* -------------------------------------------------------------------- */
	/**
	@depends testLogsFolderFullCheck
	@depends testIsSingletone
	*/
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
	/* -------------------------------------------------------------------- */
	/* ------------------- seting error stops executing ------------------- */
	/* -------------------------------------------------------------------- */
	/** @depends testIsSingletone */
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