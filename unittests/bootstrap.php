<?php
declare(strict_types=1);
/** ***********************************************************************************************
 * unit tests bootstrap file
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';

spl_autoload_register(function($className)
{
    $classNameExplode   = explode('\\', $className);
    $classShortName     = array_pop($classNameExplode);
    $classNameString    = strtolower(implode('\\', $classNameExplode)).'\\'.$classShortName;
    $classNameString    = str_replace('\\', DIRECTORY_SEPARATOR, $classNameString);
    $classFilePath      = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$classNameString.'.php';
    $file               = new SplFileInfo($classFilePath);

    if ($file->isFile() && $file->getExtension() == 'php')
    {
        include $file->getPathname();
    }
});