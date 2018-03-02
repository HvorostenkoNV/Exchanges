<?php
declare(strict_types=1);

namespace Main\Helpers;

use
	DomainException,
	RuntimeException,
	UnexpectedValueException,
	InvalidArgumentException,
	RecursiveIteratorIterator,
	RecursiveDirectoryIterator;
/**************************************************************************************************
 * Localization class, helps with application localization
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class Localization
{
	private
		$lang       = '',
		$messages   = [];
	/** **********************************************************************
	 * constructor
	 * @param   string  $lang               language code
	 * @throws  InvalidArgumentException    language code not exist
	 * @throws  DomainException             need files resources unreachable
	 ************************************************************************/
	public function __construct(string $lang)
	{
		$folder             = Config::getInstance()->getParam('main.localizationFolder');
		$localizationPath   = DOCUMENT_ROOT.DS.$folder.DS.$lang;

		if( strlen($folder) <= 0 )  throw new DomainException('Localization folder param not found');
		if( strlen($lang) <= 0 )    throw new InvalidArgumentException('Language argument empty');

		try
		{
			$directory  = new RecursiveDirectoryIterator($localizationPath);
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
						[$localizationPath.DS,  '.php', DS],
						['',                    '',     '.'],
						$filePath
					);

					if( is_array($fileContent) )
						foreach( $fileContent as $index => $value )
							$this->messages[$libraryName.'.'.$index] = $value;
				}

				$iterator->next();
			}

			$this->lang = $lang;
			Logger::getInstance()->addNotice('Localization object for "'.$lang.'" language created');
		}
		catch( UnexpectedValueException|RuntimeException $exception )
		{
			throw new DomainException($exception->getMessage());
		}
	}
	/** **********************************************************************
	 * get message
	 * @param   string  $message    need message full name
	 * @return  string              message value
	 * @example                     $loc->getMessage('errors.someErrorType')
	 * @example                     $loc->getMessage('main.important.someImportantMessage')
	 ************************************************************************/
	public function getMessage(string $message) : string
	{
		if( array_key_exists($message, $this->messages) )
			return $this->messages[$message];
		else
		{
			Logger::getInstance()->addWarning('Localization message "'.$message.'" for "'.$this->lang.'" language was not founded');
			return '';
		}
	}
}