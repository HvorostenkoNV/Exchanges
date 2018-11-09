<?php
declare(strict_types=1);

namespace UnitTests\TempDataGeneration;

use
    UnitTests\TempDataGeneration\Exceptions\GenerationException,
    UnitTests\TempDataGeneration\Exceptions\DBRecordsGenerationException,
    UnitTests\TempDataGeneration\Exceptions\ClassesGenerationException,
    UnitTests\TempDataGeneration\Exceptions\XmlGenerationException,
    UnitTests\TempDataGeneration\Exceptions\UnknownTempProcedureException,
    UnitTests\TempDataGeneration\Exceptions\UnknownTempParticipantException,
    SplFileInfo,
    UnitTests\TempDataGeneration\TempClasses\Procedure      as TempProcedure,
    UnitTests\TempDataGeneration\TempClasses\Participant    as TempParticipant;
/** ***********************************************************************************************
 * Class for creating project temp data for testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class Generator
{
    private
        $structureGenerator         = null,
        $dbStructureGenerator       = null,
        $filesStructureGenerator    = null,
        $participantsDataGenerator  = null,
        $participantsXmlGenerator   = null;
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        $this->structureGenerator           = new StructureGenerator;
        $this->dbStructureGenerator         = new DBStructureGenerator($this->structureGenerator);
        $this->filesStructureGenerator      = new FilesStructureGenerator($this->structureGenerator);
        $this->participantsDataGenerator    = new ParticipantsDataGenerator($this->structureGenerator, $this->dbStructureGenerator);
        $this->participantsXmlGenerator     = new ParticipantsXmlGenerator($this->participantsDataGenerator);
    }
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __destruct()
    {
        $this->clean();
    }
    /** **********************************************************************
     * generate project temp data
     *
     * @return  void
     * @throws  GenerationException                 generating error
     ************************************************************************/
    public function generate() : void
    {
        try
        {
            $this->structureGenerator->generate();
            $this->dbStructureGenerator->generate();
            $this->filesStructureGenerator->generate();
            $this->participantsDataGenerator->generate();
            $this->participantsXmlGenerator->generate();
        }
        catch (DBRecordsGenerationException $exception)
        {
            $error = $exception->getMessage();
            throw new GenerationException("Database writing error: $error");
        }
        catch (ClassesGenerationException $exception)
        {
            $error = $exception->getMessage();
            throw new GenerationException("Classes generation error: $error");
        }
        catch (XmlGenerationException $exception)
        {
            $error = $exception->getMessage();
            throw new GenerationException("XML generation error: $error");
        }
    }
    /** **********************************************************************
     * clear project temp data
     *
     * @return void
     ************************************************************************/
    public function clean() : void
    {
        $this->dbStructureGenerator->clear();
        $this->filesStructureGenerator->clear();
        $this->participantsDataGenerator->clear();
        $this->participantsXmlGenerator->clear();
    }
    /** **********************************************************************
     * get temp generated procedures structure
     *
     * @return  array                               temp generated procedures structure
     * @example
     *  [
     *      procedureCode   => procedureStructure,
     *      procedureCode   => procedureStructure
     *  ]
     ************************************************************************/
    public function getProcedures() : array
    {
        return $this->structureGenerator->getProcedures();
    }
    /** **********************************************************************
     * get temp generated participants structure
     *
     * @return  array                               temp generated participants structure
     * @example
     *  [
     *      participantCode => participantStructure,
     *      participantCode => participantStructure
     *  ]
     ************************************************************************/
    public function getParticipants() : array
    {
        return $this->structureGenerator->getParticipants();
    }
    /** **********************************************************************
     * get temp generated procedure participants code array
     *
     * @param   string $procedureCode               procedure code
     * @return  string[]                            temp generated procedure participants code array
     ************************************************************************/
    public function getProcedureParticipants(string $procedureCode) : array
    {
        return $this->structureGenerator->getProcedureParticipants($procedureCode);
    }
    /** **********************************************************************
     * get temp generated participants fields structure
     *
     * @param   string $participantCode             participant code
     * @return  array                               participants fields structure
     * @example
     *  [
     *      participantFieldName    => participantFieldStructure,
     *      participantFieldName    => participantFieldStructure
     *  ]
     ************************************************************************/
    public function getParticipantFields(string $participantCode) : array
    {
        return $this->structureGenerator->getParticipantFields($participantCode);
    }
    /** **********************************************************************
     * get temp generated procedure fields structure
     *
     * @param   string $procedureCode               procedure code
     * @return  array                               procedure fields structure
     * @example
     *  [
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ],
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ]
     *  ]
     ************************************************************************/
    public function getProcedureFields(string $procedureCode) : array
    {
        return $this->structureGenerator->getProcedureFields($procedureCode);
    }
    /** **********************************************************************
     * get temp generated procedure matching rules structure
     *
     * @param   string $procedureCode               procedure code
     * @return  array                               procedure matching rules structure
     * @example
     *  [
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ],
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ]
     *  ]
     ************************************************************************/
    public function getProcedureMatchingRules(string $procedureCode) : array
    {
        return $this->structureGenerator->getProcedureMatchingRules($procedureCode);
    }
    /** **********************************************************************
     * get temp generated procedure combining rules structure
     *
     * @param   string  $procedureCode              procedure code
     * @return  array                               procedure combining rules structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ]
     *  ]
     ************************************************************************/
    public function getProcedureCombiningRules(string $procedureCode) : array
    {
        return $this->structureGenerator->getProcedureCombiningRules($procedureCode);
    }
    /** **********************************************************************
     * get participant temp exist data structure
     *
     * @param   string  $participantCode            participant code
     * @return  array                               participant temp exist data structure
     * @example
     *  [
     *      participantItemId   => participantItemData,
     *      participantItemId   => participantItemData
     *  ]
     ************************************************************************/
    public function getParticipantExistData(string $participantCode) : array
    {
        return $this->participantsDataGenerator->getParticipantExistData($participantCode);
    }
    /** **********************************************************************
     * get participant temp geted data structure
     *
     * @param   string  $participantCode            participant code
     * @return  array                               participant temp geted data structure
     * @example
     *  [
     *      participantItemId   => participantItemData,
     *      participantItemId   => participantItemData
     *  ]
     ************************************************************************/
    public function getParticipantGetedData(string $participantCode) : array
    {
        return $this->participantsDataGenerator->getParticipantGetedData($participantCode);
    }
    /** **********************************************************************
     * get procedure temp matched data structure
     *
     * @param   string $procedureCode               procedure code
     * @return  array                               procedure temp matched data structure
     * @example
     *  [
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ],
     *      [
     *          participantCode => participantItemId,
     *          participantCode => participantItemId
     *      ]
     *  ]
     ************************************************************************/
    public function getProcedureMatchedData(string $procedureCode) : array
    {
        return $this->participantsDataGenerator->getProcedureMatchedData($procedureCode);
    }
    /** **********************************************************************
     * get procedure temp combined data structure
     *
     * @param   string $procedureCode               procedure code
     * @return  array                               procedure temp combined data structure
     * @example
     *  [
     *      [
     *          procedureFieldName  => value,
     *          procedureFieldName  => value
     *      ],
     *      [
     *          procedureFieldName  => value,
     *          procedureFieldName  => value
     *      ]
     *  ]
     ************************************************************************/
    public function getProcedureCombinedData(string $procedureCode) : array
    {
        return $this->participantsDataGenerator->getProcedureCombinedData($procedureCode);
    }
    /** **********************************************************************
     * get participant temp provided XML file
     *
     * @param   string  $procedureCode              procedure code
     * @param   string  $participantCode            participant code
     * @return  SplFileInfo                         participant temp provided XML file
     ************************************************************************/
    public function getParticipantXml(string $procedureCode, string $participantCode) : SplFileInfo
    {
        return $this->participantsXmlGenerator->getXml($procedureCode, $participantCode);
    }
    /** **********************************************************************
     * construct temp procedure
     *
     * @param   string $procedureCode               procedure code
     * @return  TempProcedure                       temp procedure
     * @throws  UnknownTempProcedureException       temp procedure constructing error
     ************************************************************************/
    public function constructProcedure(string $procedureCode) : TempProcedure
    {
        try
        {
            return $this->filesStructureGenerator->constructProcedure($procedureCode);
        }
        catch (UnknownTempProcedureException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * construct temp participant
     *
     * @param   string $participantCode             participant code
     * @return  TempParticipant                     temp participant
     * @throws  UnknownTempParticipantException     temp participant constructing error
     ************************************************************************/
    public function constructParticipant(string $participantCode) : TempParticipant
    {
        try
        {
            return $this->filesStructureGenerator->constructParticipant($participantCode);
        }
        catch (UnknownTempParticipantException $exception)
        {
            throw $exception;
        }
    }
}