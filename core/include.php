<?php
declare(strict_types=1);

use Main\Helpers\Logger;
/** ***********************************************************************************************
 * main bootstrap file
 *
 * @package exchange
 * @author  Hvorostenko
 *************************************************************************************************/
define('DOCUMENT_ROOT',     $_SERVER['DOCUMENT_ROOT']);
define('PARAMS_FOLDER',     'params');
define('CLASSES_FOLDER',    'core');

spl_autoload_register(function($className)
{
    $classNameString    = strtolower($className);
    $classNameString    = str_replace('\\', DIRECTORY_SEPARATOR, $classNameString);
    $classesFolderPath  = DOCUMENT_ROOT.DIRECTORY_SEPARATOR.CLASSES_FOLDER;
    $classFilePath      = $classesFolderPath.DIRECTORY_SEPARATOR.$classNameString.'.php';
    $file               = new SplFileInfo($classFilePath);

    if ($file->isFile() && $file->getExtension() == 'php')
    {
        include $file->getPathname();
    }
    else
    {
        try
        {
            Logger::getInstance()->addWarning("Trying to load unfounded class \"$className\"");
        }
        catch (Error $exception)
        {

        }
    }
});