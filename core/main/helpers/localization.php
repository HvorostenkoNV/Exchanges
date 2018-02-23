<?php
namespace Main\Helpers;

use
	DomainException,
	RuntimeException,
	UnexpectedValueException,
	InvalidArgumentException,
	RecursiveIteratorIterator,
	RecursiveDirectoryIterator;

class Localization
{
	private
		$lang       = '',
		$messages   = [];
	/* -------------------------------------------------------------------- */
	/* ---------------------------- construct ----------------------------- */
	/* -------------------------------------------------------------------- */
	public function __construct(string $lang)
	{
		$folder             = Config::getInstance()->getParam('main.localizationFolder');
		$localizationPath   = DOCUMENT_ROOT.DS.$folder.DS.$lang;

		if( strlen($folder) <= 0 )  throw new DomainException('Localization folder param not found');
		if( strlen($lang) <= 0 )    throw new InvalidArgumentException('Language argument empty');

		try
		{
			foreach( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($localizationPath)) as $file )
				if( $file->isFile() && $file->isReadable() && $file->getExtension() == 'php' )
				{
					$libraryContent = include $file->getPathname();
					$libraryName    = str_replace
					(
						[$localizationPath.DS,  '.php', DS],
						['',                    '',     '.'],
						$file->getPathname()
					);

					if( is_array($libraryContent) )
						foreach( $libraryContent as $index => $value )
							$this->messages[$libraryName.'.'.$index] = $value;
				}
		}
		catch( UnexpectedValueException|RuntimeException $exception )
		{
			throw new DomainException($exception->getMessage());
		}

		$this->lang = $lang;
		Logger::getInstance()->addNotice('Localization object for "'.$lang.'" language created');
	}
	/* -------------------------------------------------------------------- */
	/* ---------------------------- getMessage ---------------------------- */
	/* -------------------------------------------------------------------- */
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