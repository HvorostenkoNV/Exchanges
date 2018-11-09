<?php
declare(strict_types=1);

namespace UnitTests\TempDataGeneration;

use
    Throwable,
    ReflectionException,
    UnitTests\TempDataGeneration\Exceptions\ClassesGenerationException,
    UnitTests\TempDataGeneration\Exceptions\UnknownTempProcedureException,
    UnitTests\TempDataGeneration\Exceptions\UnknownTempParticipantException,
    ReflectionClass,
    SplFileInfo,
    UnitTests\TempDataGeneration\TempClasses\Procedure      as TempProcedure,
    UnitTests\TempDataGeneration\TempClasses\Participant    as TempParticipant;
/** ***********************************************************************************************
 * Class for creating project temp files structure
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class FilesStructureGenerator
{
    private
        $structureGenerator = null,
        $generatedClasses   =
        [
            'procedures'    => [],
            'participants'  => []
        ],
        $createdObjects     =
        [
            'procedures'    => [],
            'participants'  => []
        ];
    /** **********************************************************************
     * constructor
     *
     * @param   StructureGenerator $structureGenerator      structure generator
     ************************************************************************/
    public function __construct(StructureGenerator $structureGenerator)
    {
        $this->structureGenerator = $structureGenerator;
    }
    /** **********************************************************************
     * generate project temp files structure
     *
     * @return  void
     * @throws  ClassesGenerationException                  generation error
     ************************************************************************/
    public function generate() : void
    {
        try
        {
            $procedures     = array_keys($this->structureGenerator->getProcedures());
            $participants   = array_keys($this->structureGenerator->getParticipants());

            foreach ($procedures as $procedureCode)
            {
                $this->generatedClasses['procedures'][$procedureCode] = $this->generateProcedureClass($procedureCode);
            }
            foreach ($participants as $participantCode)
            {
                $this->generatedClasses['participants'][$participantCode] = $this->generateParticipantClass($participantCode);
            }
        }
        catch (ClassesGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * construct temp created procedure
     *
     * @param   string $procedureCode                       procedure code
     * @return  TempProcedure                               temp procedure
     * @throws  UnknownTempProcedureException               temp procedure constructing error
     ************************************************************************/
    public function constructProcedure(string $procedureCode) : TempProcedure
    {
        if (!array_key_exists($procedureCode, $this->generatedClasses['procedures']))
        {
            throw new UnknownTempProcedureException;
        }

        if (!array_key_exists($procedureCode, $this->createdObjects['procedures']))
        {
            try
            {
                $procedureClassName = $this->generatedClasses['procedures'][$procedureCode];
                $this->createdObjects['procedures'][$procedureCode] = new $procedureClassName;
            }
            catch (Throwable $exception)
            {
                throw new UnknownTempProcedureException;
            }
        }

        return $this->createdObjects['procedures'][$procedureCode];
    }
    /** **********************************************************************
     * construct temp created participant
     *
     * @param   string $participantCode                     participant code
     * @return  TempParticipant                             temp participant
     * @throws  UnknownTempParticipantException             temp participant constructing error
     ************************************************************************/
    public function constructParticipant(string $participantCode) : TempParticipant
    {
        if (!array_key_exists($participantCode, $this->generatedClasses['participants']))
        {
            throw new UnknownTempParticipantException;
        }

        if (!array_key_exists($participantCode, $this->createdObjects['participants']))
        {
            try
            {
                $procedureClassName = $this->generatedClasses['participants'][$participantCode];
                $this->createdObjects['participants'][$participantCode] = new $procedureClassName;
            }
            catch (Throwable $exception)
            {
                throw new UnknownTempParticipantException;
            }
        }

        return $this->createdObjects['participants'][$participantCode];
    }
    /** **********************************************************************
     * clear temp files structure
     *
     * @return void
     ************************************************************************/
    public function clear() : void
    {
        foreach ($this->generatedClasses as $classesGroup)
        {
            foreach ($classesGroup as $classFilePath)
            {
                $classFile = new SplFileInfo($classFilePath);
                if ($classFile->isFile())
                {
                    unlink($classFile->getPathname());
                }
            }
        }
    }
    /** **********************************************************************
     * generate temp procedure class
     *
     * @param   string $procedureCode                       procedure code
     * @return  string                                      created temp procedure class path
     * @throws  ClassesGenerationException                  temp class generation error
     ************************************************************************/
    private function generateProcedureClass(string $procedureCode) : string
    {
        try
        {
            return $this->generateNewClass(TempProcedure::class, $procedureCode);
        }
        catch (ClassesGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * generate temp participant class
     *
     * @param   string $participantCode                     participant code
     * @return  string                                      created temp participant class path
     * @throws  ClassesGenerationException                  temp class generation error
     ************************************************************************/
    private function generateParticipantClass(string $participantCode) : string
    {
        try
        {
            return $this->generateNewClass(TempParticipant::class, $participantCode);
        }
        catch (ClassesGenerationException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * generate new temp class
     *
     * @param   string  $parentClassFullName                parent class full name
     * @param   string  $classShortName                     class short name
     * @return  string                                      created temp class path
     * @throws  ClassesGenerationException                  temp class generation error
     ************************************************************************/
    private function generateNewClass(string $parentClassFullName, string $classShortName) : string
    {
        try
        {
            $reflection     = new ReflectionClass($parentClassFullName);
            $namespace      = $reflection->getNamespaceName();
            $classFullName  = $namespace.'\\'.$classShortName;
            $classContent   = $this->generateNewClassContent($namespace, $classShortName, $parentClassFullName);

            $this->saveNewClass($classFullName, $classContent);

            return $classFullName;
        }
        catch (ClassesGenerationException $exception)
        {
            throw $exception;
        }
        catch (ReflectionException $exception)
        {
            throw new ClassesGenerationException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * generate class content template
     *
     * @param   string  $classNamespace                     class namespace
     * @param   string  $classShortName                     class short name
     * @param   string  $parentClassFullName                parent class full name
     * @return  string                                      class content template
     ************************************************************************/
    private function generateNewClassContent(string $classNamespace, string $classShortName, string $parentClassFullName) : string
    {
        $content        = "
            <?php
            declare(strict_types=1);

            namespace $classNamespace;

            class $classShortName extends \\$parentClassFullName
            {

            }";
        $contentExplode = explode("\n", $content);
        $contentExplode = array_map
        (
            function($value) {return trim($value);},
            $contentExplode
        );
        $contentExplode = array_filter
        (
            $contentExplode,
            function($value) {return strlen($value) > 0;}
        );

        return implode("\n", $contentExplode);
    }
    /** **********************************************************************
     * generate new temp class
     *
     * @param   string  $classFullName                      class full name
     * @param   string  $classContent                       class content
     * @return  void
     * @throws  ClassesGenerationException                  new temp class generating error
     ************************************************************************/
    private function saveNewClass(string $classFullName, string $classContent) : void
    {
        $classNameExplode   = explode('\\', $classFullName);
        $classShortName     = array_pop($classNameExplode);
        $classDirectoryPath =
            $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
            strtolower(implode(DIRECTORY_SEPARATOR, $classNameExplode));
        $classFilePath      = $classDirectoryPath.DIRECTORY_SEPARATOR.$classShortName.'.php';
        $classDirectory     = new SplFileInfo($classDirectoryPath);
        $classFile          = new SplFileInfo($classFilePath);

        if (!$classDirectory->isDir())
        {
            @mkdir($classDirectoryPath, 0777, true);
        }
        if (!$classDirectory->isDir())
        {
            throw new ClassesGenerationException("creating directory \"$classDirectoryPath\" failed");
        }
        if (!$classDirectory->isWritable())
        {
            throw new ClassesGenerationException("directory \"$classDirectoryPath\" is not writable");
        }

        $creatingSuccess = $classFile->openFile('w')->fwrite($classContent);
        if ($creatingSuccess <= 0)
        {
            throw new ClassesGenerationException("file \"$classFilePath\" was not created");
        }
    }
}