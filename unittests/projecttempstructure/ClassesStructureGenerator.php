<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use
    RuntimeException,
    SplFileInfo,
    ReflectionClass,
    ReflectionMethod,
    ReflectionType,
    ReflectionParameter,
    Main\Exchange\Participants\AbstractParticipant,
    Main\Exchange\Procedures\AbstractProcedure;
/** ***********************************************************************************************
 * Class for creating project temp classes structure
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ClassesStructureGenerator
{
    private
        $proceduresParentClass      = AbstractProcedure::class,
        $participantsParentClass    = AbstractParticipant::class,
        $tempClasses                = [];
    /** **********************************************************************
     * generate classes structure
     *
     * @param   array $structure                    generated logic structure
     * @return  array                               generated classes structure
     * @throws  RuntimeException                    generating error
     ************************************************************************/
    public function generate(array $structure) : array
    {
        $result                 = [];
        $proceduresNamespace    = (new ReflectionClass(AbstractProcedure::class))->getNamespaceName();
        $participantsNamespace  = (new ReflectionClass(AbstractParticipant::class))->getNamespaceName();

        try
        {
            foreach ($structure as $procedureCode => $procedureInfo)
            {
                $procedureClassName = $proceduresNamespace.'\\'.$procedureCode;
                $procedureFile      = $this->generateTempClass($procedureClassName, $this->proceduresParentClass);

                $result[$procedureCode] =
                [
                    'file'          => $procedureFile,
                    'class'         => $procedureClassName,
                    'participants'  => []
                ];

                foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
                {
                    $participantClassName   = $participantsNamespace.'\\'.$participantCode;
                    $participantFile        = $this->generateTempClass($participantClassName, $this->participantsParentClass);

                    $result[$procedureCode]['participants'][$participantCode] =
                    [
                        'file'  => $participantFile,
                        'class' => $participantClassName
                    ];
                }
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * clean temp classes
     ************************************************************************/
    public function clean() : void
    {
        foreach ($this->tempClasses as $classFile)
        {
            if ($classFile->isFile())
            {
                unlink($classFile->getPathname());
            }
        }
    }
    /** **********************************************************************
     * set procedure parent class
     *
     * @param   string $className                   procedure parent class
     ************************************************************************/
    public function setProcedureParentClass(string $className) : void
    {
        if (strlen($className) > 0)
        {
            $this->proceduresParentClass = $className;
        }
    }
    /** **********************************************************************
     * set participant parent class
     *
     * @param   string $className                   participant parent class
     ************************************************************************/
    public function setParticipantParentClass(string $className) : void
    {
        if (strlen($className) > 0)
        {
            $this->participantsParentClass = $className;
        }
    }
    /** **********************************************************************
     * create temp class file
     *
     * @param   string      $name                   class name
     * @param   string|null $parent                 parent class for extend
     * @return  SplFileInfo                         created file
     * @throws  RuntimeException                    creating error
     ************************************************************************/
    public function generateTempClass(string $name, ?string $parent) : SplFileInfo
    {
        if (is_string($parent) && strlen($parent) && !class_exists($parent))
        {
            throw new RuntimeException("Parent class \"$parent\" was not found");
        }

        $classFile      = $this->createClassFile($name);
        $classContent   = $this->createClassContent($name, $parent);

        $classFile->openFile('w')->fwrite($classContent);
        if (!class_exists($name))
        {
            throw new RuntimeException("Created class \"$name\" was not included or created with error");
        }

        $this->tempClasses[] = $classFile;
        return $classFile;
    }
    /** **********************************************************************
     * get class file
     *
     * @param   string $name                        class name
     * @return  SplFileInfo                         class file
     ************************************************************************/
    private function createClassFile(string $name) : SplFileInfo
    {
        $classNameExplode   = explode('\\', $name);
        $classShortName     = array_pop($classNameExplode);
        $classNameString    = strtolower(implode('\\', $classNameExplode)).'\\'.$classShortName;
        $classNameString    = str_replace('\\', DIRECTORY_SEPARATOR, $classNameString);
        $classFilePath      = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$classNameString.'.php';
        $file               = new SplFileInfo($classFilePath);
        $directory          = new SplFileInfo($file->getPath());

        if (!$directory->isDir())
        {
            mkdir($file->getPath(), 0777, true);
        }
        if (!$file->isFile())
        {
            $file->openFile('w')->fwrite('');
        }

        return $file;
    }
    /** **********************************************************************
     * get class content
     *
     * @param   string      $name                   full class name
     * @param   string|null $parent                 parent class for extend
     * @return  string                              class content
     ************************************************************************/
    private function createClassContent(string $name, ?string $parent) : string
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