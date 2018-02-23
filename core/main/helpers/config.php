<?php
namespace Main\Helpers;

use
	RuntimeException,
	UnexpectedValueException,
	RecursiveIteratorIterator,
	RecursiveDirectoryIterator,
	Main\Singltone;

class Config
{
	use Singltone;

	private $params = [];
	/* -------------------------------------------------------------------- */
	/* ---------------------------- construct ----------------------------- */
	/* -------------------------------------------------------------------- */
	private function __construct()
	{
		try
		{
			foreach( new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PARAMS_FOLDER)) as $file )
				if( $file->isFile() && $file->isReadable() && $file->getExtension() == 'php' )
				{
					$libraryContent = include $file->getPathname();
					$libraryName    = str_replace
					(
						[PARAMS_FOLDER.DS,    '.php', DS],
						['',                '',     '.'],
						$file->getPathname()
					);

					if( is_array($libraryContent) )
						foreach( $libraryContent as $index => $value )
							$this->params[$libraryName.'.'.$index] = $value;
				}
		}
		catch( UnexpectedValueException|RuntimeException $exception )
		{
			Logger::getInstance()->addError('Params folder was not founded');
		}

		Logger::getInstance()->addNotice('Config object created');
	}
	/* -------------------------------------------------------------------- */
	/* ----------------------------- getParam ----------------------------- */
	/* -------------------------------------------------------------------- */
	public function getParam(string $paramName) : string
	{
		if( array_key_exists($paramName, $this->params) )
			return $this->params[$paramName];
		else
		{
			Logger::getInstance()->addWarning('Trying to get unknown param "'.$paramName.'"');
			return '';
		}
	}
}