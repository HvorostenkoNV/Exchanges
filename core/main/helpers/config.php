<?php
declare(strict_types=1);

namespace Main\Helpers;

use
	RuntimeException,
	UnexpectedValueException,
	RecursiveIteratorIterator,
	RecursiveDirectoryIterator,
	Main\Singleton;
/**************************************************************************************************
 * Config class, provides access to system configs params
 * @package exchange_helpers
 * @method  static Config getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class Config
{
	use Singleton;

	private $params = [];
	/** **********************************************************************
	 * constructor
	 ************************************************************************/
	private function __construct()
	{
		try
		{
			$directory  = new RecursiveDirectoryIterator(PARAMS_FOLDER);
			$iterator   = new RecursiveIteratorIterator($directory);

			while( $iterator->valid() )
			{
				$item = $iterator->current();

				if( $item->isFile() && $item->isReadable() && $item->getExtension() == 'php' )
				{
					$filePath       = $item->getPathname();
					$fileContent    = include $filePath;
					$libraryName    = str_replace
					(
						[PARAMS_FOLDER.DS,  '.php', DS],
						['',                '',     '.'],
						$filePath
					);

					if( is_array($fileContent) )
						foreach( $fileContent as $index => $value )
							$this->params[$libraryName.'.'.$index] = $value;
				}

				$iterator->next();
			}

			Logger::getInstance()->addNotice('Config object created');
		}
		catch( UnexpectedValueException|RuntimeException $exception )
		{
			Logger::getInstance()->addError('Params folder was not founded');
		}
	}
	/** **********************************************************************
	 * get parameter
	 * @param   string  $paramName  parameter name
	 * @return  string              parameter value
	 * @example                     $config->getParam('main.someImportantParameter')
	 * @example                     $config->getParam('users.connectionAD.login')
	 ************************************************************************/
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