<?php
declare(strict_types=1);
/** ***********************************************************************************************
 * unit tests bootstrap file
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';

define('UNITTESTS_ROOT',            $_SERVER['UNITTESTS_ROOT']);
define('UNITTESTS_CLASSES_FOLDER',  UNITTESTS_ROOT.DIRECTORY_SEPARATOR.'core');

spl_autoload_register(function($className)
{
    $classNameString    = strtolower($className);
    $classNameString    = str_replace('\\', DIRECTORY_SEPARATOR, $classNameString);
    $classFilePath      = UNITTESTS_CLASSES_FOLDER.DIRECTORY_SEPARATOR.$classNameString.'.php';
    $file               = new SplFileInfo($classFilePath);

    if ($file->isFile() && $file->getExtension() == 'php')
        include $file->getPathname();
});