<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use
    RuntimeException,
    SplFileInfo,
    ReflectionClass,
    ReflectionMethod,
    ReflectionType,
    ReflectionParameter;
/** ***********************************************************************************************
 * Class for generating classes temp files
 * using in UNIT-testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ClassesGenerator
{
    private $tempClasses = [];
    /** **********************************************************************
     * create temp class file
     *
     * @param   string      $name                   full class name
     * @param   string|null $parent                 parent class for extend
     * @return  void
     * @throws  RuntimeException                    creating error
     ************************************************************************/
    public function create(string $name, ?string $parent) : void
    {
        $classFile      = $this->getClassFile($name);
        $classContent   = $this->getClassContent($name, $parent);

        if (is_string($parent) && strlen($parent) && !class_exists($parent))
        {
            throw new RuntimeException("Parent class \"$parent\" was not found");
        }

        if (!$classFile->isFile())
        {
            $creatingFileResult = $classFile->openFile('w')->fwrite($classContent);

            if ($creatingFileResult === false)
            {
                throw new RuntimeException("File for class \"$name\" was not created");
            }

            include $classFile->getPathname();
        }

        if (!class_exists($name))
        {
            throw new RuntimeException("Created class \"$name\" was not included or created with error");
        }

        $this->tempClasses[] = $classFile;
    }
    /** **********************************************************************
     * drop created temp classes
     *
     * @return void
     ************************************************************************/
    public function clean() : void
    {
        foreach ($this->tempClasses as $file)
        {
            if ($file->isFile())
            {
                unlink($file->getPathname());
            }
        }
    }
    /** **********************************************************************
     * get class file
     *
     * @param   string $name                        full class name
     * @return  SplFileInfo                         class file
     ************************************************************************/
    private function getClassFile(string $name) : SplFileInfo
    {
        $classNameExplode   = explode('\\', $name);
        $classShortName     = array_pop($classNameExplode);
        $classNameString    = strtolower(implode('\\', $classNameExplode)).'\\'.$classShortName;
        $classNameString    = str_replace('\\', DIRECTORY_SEPARATOR, $classNameString);
        $classFilePath      = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.$classNameString.'.php';

        return new SplFileInfo($classFilePath);
    }
    /** **********************************************************************
     * get class content
     *
     * @param   string      $name                   full class name
     * @param   string|null $parent                 parent class for extend
     * @return  string                              class content
     ************************************************************************/
    private function getClassContent(string $name, ?string $parent) : string
    {
        $nameExplode    = explode('\\', $name);
        $name           = array_pop($nameExplode);
        $namespace      = implode('\\', $nameExplode);
        $extending      = '';
        $methods        = '';

        if (is_string($parent) && strlen($parent))
        {
            $extending          = "extends \\$parent";
            $reflection         = new ReflectionClass($parent);
            $abstractMethods    = $reflection->getMethods(ReflectionMethod::IS_ABSTRACT);

            foreach ($abstractMethods as $method)
            {
                $methods .= $this->getMethodAsString($method)."\n";
            }
        }

        $classContent = "
            <?php
            declare(strict_types=1);

            namespace $namespace;

            class $name $extending
            {
                $methods
            }";

        $classContent = trim(str_replace("\t", '', $classContent));
        return $classContent;
    }
    /** **********************************************************************
     * get class method as string
     *
     * @param   ReflectionMethod $method            method
     * @return  string                              method as string
     ************************************************************************/
    private function getMethodAsString(ReflectionMethod $method) : string
    {
        $methodReturnType   = $method->getReturnType();
        $methodParams       = $method->getParameters();
        $name               = $method->getName();
        $static             = $method->isStatic()   ? 'static'  : '';
        $modifier           = $method->isPublic()   ? 'public'  : 'protected';
        $returnType         = $method->hasReturnType() ? ': '.$this->getReturnTypeAsString($methodReturnType) : '';
        $params             = '';

        if (count($methodParams) > 0)
        {
            $paramsToPrintArray = [];
            foreach ($methodParams as $param)
            {
                $paramsToPrintArray[] = $this->getMethodParameterAsString($param);
            }
            $params = implode(', ', $paramsToPrintArray);
        }

        return "$modifier $static function $name($params) $returnType {}";
    }
    /** **********************************************************************
     * get class method return type as string
     *
     * @param   ReflectionType $type                method return type
     * @return  string                              method return type as string
     ************************************************************************/
    private function getReturnTypeAsString(ReflectionType $type) : string
    {
        $result = $type->__toString();

        if (!$type->isBuiltin())
        {
            $result = '\\'.$result;
        }
        if ($type->allowsNull())
        {
            $result = '?'.$result;
        }

        return $result;
    }
    /** **********************************************************************
     * get class method parameter as string
     *
     * @param   ReflectionParameter $parameter      method parameter
     * @return  string                              method parameter as string
     ************************************************************************/
    private function getMethodParameterAsString(ReflectionParameter $parameter) : string
    {
        $parameterReturnType    = $parameter->getType();
        $returnType             = $parameter->hasType() ? $this->getReturnTypeAsString($parameterReturnType).' ' : '';
        $name                   = $parameter->getName();

        return "$returnType$$name";
    }
}