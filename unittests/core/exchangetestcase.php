<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
/** ***********************************************************************************************
 * Main Exchange TestCase to inherit
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class ExchangeTestCase extends TestCase
{
	protected $messages =
	[
		'SINGLETON_IMPLEMENTATION_FAILED'   => 'There is a possibility to create more than one instance of #CLASS_NAME# class',
		'CONSTANT_NOT_DEFINED'              => 'Constant "#CONSTANT_NAME#" is not defined',
		'WRONG_PERMISSIONS'                 => 'File/dir has wrong permissions by path "#PATH#". Need - #NEED#. Current - #CURRENT#',
		'WRONG_EXTENSION'                   => 'File has wrong extension by path "#PATH#". Need - #NEED#. Current - #CURRENT#',
		'FILE_MUST_RETURN_ARRAY'            => 'File must return array by path "#PATH#',
		'NOT_EXIST'                         => 'File/dir is not exist by path "#PATH#"',
		'NOT_READABLE'                      => 'File/dir is not readable by path "#PATH#"',
		'NOT_WRITABLE'                      => 'File/dir is not writable by path "#PATH#"'
	];
	/** **********************************************************************
	 * get message
	 * @param   string  $type       message index
	 * @param   array   $changing   array of replacements index => value
	 * @return  string              processed message
     ************************************************************************/
	protected function getMessage(string $type, array $changing = []) : string
	{
		$result         = array_key_exists($type, $this->messages) ? $this->messages[$type] : '';
		$replaceFrom    = [];
		$replaceTo      = [];

		if( count($changing) > 0 )
			foreach( $changing as $index => $value )
			{
				$replaceFrom[]  = '#'.$index.'#';
				$replaceTo[]    = $value;
			}

		if( count($replaceFrom) > 0 && count($replaceTo) > 0 )
			$result = str_replace($replaceFrom, $replaceTo, $result);

		return $result;
	}
	/** **********************************************************************
	 * check class is singleton
	 * @param   string  $className  full class name
	 * @return  bool
	 ************************************************************************/
	protected function singletonImplemented(string $className) : bool
	{
		$hasCallMethod  = method_exists($className, 'getInstance');
		$objectCreated  = NULL;
		$objectCloned   = NULL;

		try
		{
			$objectCreated  = new $className;
			$objectCloned   = clone $objectCreated;
		}
		catch( Error $error )
		{

		}

		return $hasCallMethod && !$objectCreated && !$objectCloned;
	}
	/** **********************************************************************
	 * reset singleton instance of class
	 * @param   string  $className  full class name
	 ************************************************************************/
	protected function resetSingletonInstance(string $className) : void
	{
		$instance       = call_user_func([$className, 'getInstance']);
		$reflection     = new ReflectionClass($instance);
		$instanceProp   = $reflection->getProperty('instanceArray');

		$instanceProp->setAccessible(true);
		$instanceProp->setValue([], []);
		$instanceProp->setAccessible(false);
	}
	/** **********************************************************************
	 * get file/dir permissions
	 * @param   string  $path   full file/dir path
	 * @return  string          file/dir permissions
	 * @example                 777, 555
	 ************************************************************************/
	protected function getPermissions(string $path) : string
	{
		return substr(sprintf('%o', fileperms($path)), -3);
	}
	/** **********************************************************************
	 * get all files in dir
	 * @param   string  $path   full dir path
	 * @return  SplFileInfo[]   array of SplFileInfo objects
	 ************************************************************************/
	protected function findAllFiles(string $path) : array
	{
		$result = [];

		try
		{
			$directory  = new RecursiveDirectoryIterator($path);
			$iterator   = new RecursiveIteratorIterator($directory);

			while( $iterator->valid() )
			{
				if( $iterator->current()->isFile() )
					$result[] = $iterator->current();

				$iterator->next();
			}
		}
		catch( Throwable $error )
		{

		}

		return $result;
	}
}