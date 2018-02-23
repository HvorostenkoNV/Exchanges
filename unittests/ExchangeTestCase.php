<?php
use PHPUnit\Framework\TestCase;

class ExchangeTestCase extends TestCase
{
	protected
		$documentRoot   = DOCUMENT_ROOT_BY_UT,
		$messages       =
		[
			'SINGLETONE_IMPLEMENTATION_FAILED'  => 'There is a possibility to create more than one instance of #CLASS_NAME# class',
			'CONSTANT_NOT_DEFINED'              => 'Constant "#CONSTANT_NAME#" is not defined',
			'WRONG_PERMISSIONS'                 => 'File/dir has wrong permissions by path "#PATH#". Need - #NEED#. Current - #CURRENT#',
			'WRONG_EXTENSION'                   => 'File has wrong extension by path "#PATH#". Need - #NEED#. Current - #CURRENT#',
			'FILE_MUST_RETURN_ARRAY'            => 'File must return array by path "#PATH#'
		];
	/* -------------------------------------------------------------------- */
	/* --------------------------- get message ---------------------------- */
	/* -------------------------------------------------------------------- */
	protected function getMessage(string $type, array $changings = []) : string
	{
		$result         = array_key_exists($type, $this->messages) ? $this->messages[$type] : '';
		$replaceFrom    = [];
		$replaceTo      = [];

		if( count($changings) > 0 )
			foreach( $changings as $index => $value )
			{
				$replaceFrom[]  = '#'.$index.'#';
				$replaceTo[]    = $value;
			}

		if( count($replaceFrom) > 0 && count($replaceTo) > 0 )
			$result = str_replace($replaceFrom, $replaceTo, $result);

		return $result;
	}
	/* -------------------------------------------------------------------- */
	/* -------------------------- is singletone --------------------------- */
	/* -------------------------------------------------------------------- */
	protected function singletoneImplemented(string $className) : bool
	{
		$objectCreated  = NULL;
		$objectCloned   = NULL;

		self::assertInstanceOf($className, $className::getInstance());

		try
		{
			$objectCreated  = new $className;
			$objectCloned   = clone $objectCreated;
		}
		catch( Error $error )
		{

		}

		return !$objectCreated && !$objectCloned;
	}
	/* -------------------------------------------------------------------- */
	/* --------------------- reset singletone instance -------------------- */
	/* -------------------------------------------------------------------- */
	protected function resetSingletoneInstance(string $className) : void
	{
		$instance       = $className::getInstance();
		$reflection     = new ReflectionClass($instance);
		$instanceProp   = $reflection->getProperty('instanceArray');

		$instanceProp->setAccessible(true);
		$instanceProp->setValue([], []);
		$instanceProp->setAccessible(false);
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------- get permissions -------------------------- */
	/* -------------------------------------------------------------------- */
	protected function getPermissions(string $path) : string
	{
		return substr(sprintf('%o', fileperms($path)), -3);
	}
	/* -------------------------------------------------------------------- */
	/* -------------------------- find all files -------------------------- */
	/* -------------------------------------------------------------------- */
	protected function findAllFiles(string $path) : array
	{
		$result = [];

		foreach( new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file )
			if( $file->isFile() )
				$result[] = $file;

		return $result;
	}
}