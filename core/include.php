<?php
declare(strict_types=1);

use Main\Helpers\Logger;
/** ***********************************************************************************************
 * main bootstrap file
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
define('DOCUMENT_ROOT',     $_SERVER['DOCUMENT_ROOT']);
define('DS',                DIRECTORY_SEPARATOR);
define('PARAMS_FOLDER',     DOCUMENT_ROOT.DS.'params');
define('LOGS_FOLDER',       DOCUMENT_ROOT.DS.'logs');
define('CLASSES_FOLDER',    DOCUMENT_ROOT.DS.'core');

spl_autoload_register(function($className)
{
    $classNameString    = strtolower($className);
    $classNameString    = str_replace('\\', DS, $classNameString);
    $classFilePath      = CLASSES_FOLDER.DS.$classNameString.'.php';
    $file               = new SplFileInfo($classFilePath);
    $logger             = null;

    try
    {
        $logger = Logger::getInstance();
    }
    catch (Error $exception)
    {

    }

    if ($file->isFile() && $file->getExtension() == 'php')
    {
        include $file->getPathname();
    }
    elseif ($logger)
    {
        $logger->addWarning("Trying to load unfounded class \"$className\"");
    }
});