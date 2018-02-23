<?php
namespace Main\Helpers;

use
	DateTime,
	DateInterval,
	SplFileInfo,
	Main\Singltone;

class Logger
{
	use Singltone;

	private
		$messages       = [],
		$availableTypes = ['note', 'warning', 'error'];
	/* -------------------------------------------------------------------- */
	/* ---------------------------- construct ----------------------------- */
	/* -------------------------------------------------------------------- */
	private function __construct()
	{
		$this->addNotice('Logger object created');
	}
	/* -------------------------------------------------------------------- */
	/* --------------------------- add message ---------------------------- */
	/* -------------------------------------------------------------------- */
	public function addNotice(string $message) : void
	{
		$this->addString('note', $message);
	}

	public function addWarning(string $message) : void
	{
		$this->addString('warning', $message);
	}

	public function addError(string $message) : void
	{
		$this->addString('error', $message);
		$this->write();
		trigger_error($message, E_USER_ERROR);
	}

	private function addString(string $type, string $message) : void
	{
		if( !in_array($type, $this->availableTypes) || strlen($message) <= 0 ) return;

		$this->messages[] =
		[
			'type'      => $type,
			'message'   => $message
		];
	}
	/* -------------------------------------------------------------------- */
	/* ---------------------------- write log ----------------------------- */
	/* -------------------------------------------------------------------- */
	public function write() : void
	{
		$date       = new DateTime;
		$logsDir    = new SplFileInfo(LOGS_FOLDER);
		$logFile    = NULL;
		$messages   = [];

		while( !$logFile )
		{
			$logFile = new SplFileInfo(LOGS_FOLDER.DS.$date->format('Y-m-d_H-i-s').'.txt');
			if( $logFile->isFile() )
			{
				$logFile = NULL;
				$date->add(new DateInterval('PT1S'));
			}
		}

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
			$logFile
				->openFile('w')
				->fwrite(implode("\n", $messages));
	}
}