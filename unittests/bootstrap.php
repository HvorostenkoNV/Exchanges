<?php
declare(strict_types=1);

use UnitTests\TempDataGeneration\Generator;
/** ***********************************************************************************************
 * unit tests bootstrap file
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';

spl_autoload_register(function($className)
{
    $classNameExplode   = explode('\\', $className);
    $classShortName     = array_pop($classNameExplode);
    $classDirectoryPath = strtolower(implode(DIRECTORY_SEPARATOR, $classNameExplode));
    $classFilePath      =
        $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        $classDirectoryPath.DIRECTORY_SEPARATOR.
        $classShortName.'.php';
    $file               = new SplFileInfo($classFilePath);

    if ($file->isFile() && $file->getExtension() == 'php')
    {
        include $file->getPathname();
    }
});

$generator = new Generator;
try
{
    $generator->generate();
}
catch (Throwable $exception)
{
    $generator->clean();
    echo $exception->getMessage();
}