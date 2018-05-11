<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    Throwable,
    SplFileInfo,
    ReflectionClass,
    ReflectionException,
    RecursiveDirectoryIterator,
    RecursiveIteratorIterator,
    PHPUnit\Framework\TestCase;
/** ***********************************************************************************************
 * Main Exchange TestCase to inherit
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class ExchangeTestCase extends TestCase
{
    private static $messages =
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
     *
     * @param   string  $type               message code
     * @param   array   $changing           array of replacements index => value
     * @return  string                      processed message
     ************************************************************************/
    protected static function getMessage(string $type, array $changing = []) : string
    {
        if (!array_key_exists($type, self::$messages))
        {
            return '';
        }

        $replaceFrom    = [];
        $replaceTo      = [];

        foreach ($changing as $index => $value)
        {
            $replaceFrom[]  = '#'.$index.'#';
            $replaceTo[]    = $value;
        }

        return str_replace($replaceFrom, $replaceTo, self::$messages[$type]);
    }
    /** **********************************************************************
     * check class is singleton
     *
     * @param   string  $className          full class name
     * @return  bool                        class is singleton
     ************************************************************************/
    protected static function singletonImplemented(string $className) : bool
    {
        $hasCallMethod  = method_exists($className, 'getInstance');
        $objectCreated  = null;
        $objectCloned   = null;

        try
        {
            $objectCreated  = new $className;
            $objectCloned   = clone $objectCreated;
        }
        catch (Throwable $error)
        {

        }

        return $hasCallMethod && !$objectCreated && !$objectCloned;
    }
    /** **********************************************************************
     * reset singleton instance of class
     *
     * @param   string  $className          full class name
     ************************************************************************/
    protected static function resetSingletonInstance(string $className) : void
    {
        try
        {
            $reflection     = new ReflectionClass($className);
            $instanceProp   = $reflection->getProperty('instance');
            $propIsArray    = is_array($reflection->getDefaultProperties()['instance']);

            $instanceProp->setAccessible(true);
            $instanceProp->setValue
            (
                null,
                $propIsArray ? [] : null
            );
            $instanceProp->setAccessible(false);
        }
        catch (ReflectionException $exception)
        {

        }
    }
    /** **********************************************************************
     * get file/dir permissions
     *
     * @param   string  $path               full file/dir path
     * @return  string                      file/dir permissions
     * @example                             777, 555
     ************************************************************************/
    protected static function getPermissions(string $path) : string
    {
        return substr(sprintf('%o', fileperms($path)), -3);
    }
    /** **********************************************************************
     * get all files in dir
     *
     * @param   SplFileInfo $folder         folder
     * @return  SplFileInfo[]               folder files
     ************************************************************************/
    protected static function getAllFiles(SplFileInfo $folder) : array
    {
        $result = [];

        try
        {
            $directory  = new RecursiveDirectoryIterator($folder->getPathname());
            $iterator   = new RecursiveIteratorIterator($directory);

            while ($iterator->valid())
            {
                if ($iterator->current()->isFile())
                {
                    $result[] = $iterator->current();
                }

                $iterator->next();
            }
        }
        catch (Throwable $error)
        {

        }

        return $result;
    }
}