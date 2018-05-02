<?php
declare(strict_types=1);

namespace UnitTests\Core;

use
    Throwable,
    ReflectionException,
    ReflectionClass,
    SplFileInfo;
/** ***********************************************************************************************
 * Class for creating/deleting application temp files
 * using in UNIT-testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class TempFilesCreator
{
    private $tempFiles = [];
    /** **********************************************************************
     * create temp class file based on other class file
     * using for creating family temp class file
     *
     * @param   string  $baseClassName          base class full name
     * @param   string  $newClassName           new class short name
     * @return  bool                            creating success
     ************************************************************************/
    public function createTempClass(string $baseClassName, string $newClassName) : bool
    {
        $baseClassReflection    = null;
        $baseClassFile          = null;
        $baseClassContent       = '';

        try
        {
            $baseClassReflection    = new ReflectionClass($baseClassName);
            $baseClassFile          = new SplFileInfo($baseClassReflection->getFileName());

            if ($baseClassFile->isFile() && $baseClassFile->isReadable())
            {
                $baseClassContent = $baseClassFile->openFile('r')->fread($baseClassFile->getSize());
            }
        }
        catch (ReflectionException $exception)
        {
            return false;
        }

        $newClassFileName       = $newClassName.'.php';
        $newClassFilePath       = $baseClassFile->getPath().DIRECTORY_SEPARATOR.$newClassFileName;
        $newClassFile           = new SplFileInfo($newClassFilePath);
        $newClassQualifiedName  = $baseClassReflection->getNamespaceName().'\\'.$newClassName;
        $newClassFileContent    = str_replace($baseClassReflection->getShortName(), $newClassName, $baseClassContent);

        if (!$newClassFile->isFile())
        {
            $newClassFile
                ->openFile('w')
                ->fwrite($newClassFileContent);
        }

        try
        {
            include $newClassFile->getPathname();
            new $newClassQualifiedName;
            $this->tempFiles[] = $newClassFile->getPathname();
            return true;
        }
        catch (Throwable $exception)
        {
            return false;
        }
    }
    /** **********************************************************************
     * drop created temp files
     ************************************************************************/
    public function dropCreatedTempFiles() : void
    {
        foreach ($this->tempFiles as $filePath)
        {
            $file = new SplFileInfo($filePath);
            if ($file->isFile())
            {
                unlink($file->getPathname());
            }
        }
    }
}