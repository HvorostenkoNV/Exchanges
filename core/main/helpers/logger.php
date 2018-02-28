<?php
declare(strict_types=1);

namespace Main\Helpers;

use
	DateTime,
	DateInterval,
	SplFileInfo,
	Main\Singleton;
/**************************************************************************************************
 * Logger class, provides logging functional
 * @package exchange_helpers
 * @method  static Logger getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class Logger
{
	use Singleton;

	private
		$messages       = [],
		$availableTypes = ['note', 'warning', 'error'];
	/** **********************************************************************
	 * constructor
	 ************************************************************************/
	private function __construct()
	{
		$this->addNotice('Logger object created');
	}
	/** **********************************************************************
	 * add notice
	 * @param   string  $message    message text
	 ************************************************************************/
	public function addNotice(string $message) : void
	{
		$this->addString('note', $message);
	}
	/** **********************************************************************
	 * add warning
	 * @param   string  $message    message text
	 ************************************************************************/
	public function addWarning(string $message) : void
	{
		$this->addString('warning', $message);
	}
	/** **********************************************************************
	 * add error and shut system down
	 * @param   string  $message    message text
	 ************************************************************************/
	public function addError(string $message) : void
	{
		$this->addString('error', $message);
		$this->write();
		trigger_error($message, E_USER_ERROR);
	}
	/** **********************************************************************
	 * saving message string
	 * @param   string  $type       message type
	 * @param   string  $message    message text
	 ************************************************************************/
	private function addString(string $type, string $message) : void
	{
		if( !in_array($type, $this->availableTypes) || strlen($message) <= 0 ) return;

		$this->messages[] =
		[
			'type'      => $type,
			'message'   => $message
		];
	}
	/** **********************************************************************
	 * saving log file
	 ************************************************************************/
	public function write() : void
	{
		$date           = new DateTime;
		$logsDir        = new SplFileInfo(LOGS_FOLDER);
		$messages       = [];
		$logFileCreated = false;

		foreach( $this->messages as $messageInfo )
			switch( $messageInfo['type'] )
			{
				case 'note':
					$messages[] = $messageInfo['message'];
					break;
				case 'warning':
					$messages[] = 'WARNING: '.$messageInfo['message'];
					break;
				case 'error':
					$messages[] = 'FATAL ERROR: '.$messageInfo['message'];
					break;
			}

		if( $logsDir->isDir() && $logsDir->isWritable() )
			while( !$logFileCreated )
			{
				$logFile = new SplFileInfo(LOGS_FOLDER.DS.$date->format('Y-m-d_H-i-s').'.txt');

				if( !$logFile->isFile() )
				{
					$logFile
						->openFile('w')
						->fwrite(implode("\n", $messages));
					$logFileCreated = true;
				}
				else
					$date->add(new DateInterval('PT1S'));
			}
	}
}