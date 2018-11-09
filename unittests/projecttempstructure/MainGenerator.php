<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use RuntimeException;
/** ***********************************************************************************************
 * Class for creating project temp structure
 * creates temp structure, DB records, classes and XML files for imitating provided data
 * using in UNIT-testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class MainGenerator
{
    private
        $structureGenerator         = null,
        $dbStructureGenerator       = null,
        $classesStructureGenerator  = null,
        $providedDataGenerator      = null,
        $providedXmlDataGenerator   = null,
        $combinedDataGenerator      = null,
        $structure                  = [],
        $dbStructure                = [],
        $classesStructure           = [],
        $providedData               = [],
        $providedMatchedData        = [],
        $providedXmlData            = [],
        $combinedData               = [];
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        $this->structureGenerator           = new StructureGenerator;
        $this->dbStructureGenerator         = new DBStructureGenerator;
        $this->classesStructureGenerator    = new ClassesStructureGenerator;
        $this->providedDataGenerator        = new ProvidedDataGenerator;
        $this->providedXmlDataGenerator     = new ProvidedXmlDataGenerator;
        $this->combinedDataGenerator        = new CombinedDataGenerator;
    }
    /** **********************************************************************
     * generate project temp structure
     *
     * @return  void
     * @throws  RuntimeException            generating error
     ************************************************************************/
    public function generate() : void
    {
        $this->clean();

        try
        {
            $this->structure        = $this->structureGenerator->generate();
            $this->dbStructure      = $this->dbStructureGenerator->generate($this->structure);
            $this->classesStructure = $this->classesStructureGenerator->generate($this->structure);

            $this->providedDataGenerator->generate($this->structure, $this->dbStructure);
            $this->providedData         = $this->providedDataGenerator->getData();
            $this->providedMatchedData  = $this->providedDataGenerator->getMatchedData();
            $this->providedXmlData      = $this->providedXmlDataGenerator->generate($this->providedData);
            $this->combinedData         = $this->combinedDataGenerator->generate($this->structure, $this->providedMatchedData);
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * set procedure parent class
     *
     * @param   string $className           procedure parent class
     * @return  void
     ************************************************************************/
    public function setProcedureParentClass(string $className) : void
    {
        $this->classesStructureGenerator->setProcedureParentClass($className);
    }
    /** **********************************************************************
     * set participant parent class
     *
     * @param   string $className           participant parent class
     * @return  void
     ************************************************************************/
    public function setParticipantParentClass(string $className) : void
    {
        $this->classesStructureGenerator->setParticipantParentClass($className);
    }
    /** **********************************************************************
     * get generated temp structure
     *
     * @return  array                       generated temp structure
     ************************************************************************/
    public function getStructure() : array
    {
        return $this->structure;
    }
    /** **********************************************************************
     * get generated database temp structure
     *
     * @return  array                       generated database temp structure
     ************************************************************************/
    public function getDbStructure() : array
    {
        return $this->dbStructure;
    }
    /** **********************************************************************
     * get generated classes temp structure
     *
     * @return  array                       generated database temp structure
     ************************************************************************/
    public function getClassesStructure() : array
    {
        return $this->classesStructure;
    }
    /** **********************************************************************
     * get participants temp provided data
     *
     * @return  array                       generated participants temp provided data
     ************************************************************************/
    public function getProvidedData() : array
    {
        return $this->providedData;
    }
    /** **********************************************************************
     * get participants temp provided matched data
     *
     * @return  array                       generated participants temp provided matched data
     ************************************************************************/
    public function getProvidedMatchedData() : array
    {
        return $this->providedMatchedData;
    }
    /** **********************************************************************
     * get participants temp provided xml data
     *
     * @return  array                       generated participants temp provided xml data
     ************************************************************************/
    public function getProvidedXmlData() : array
    {
        return $this->providedXmlData;
    }
    /** **********************************************************************
     * get participants temp combined data
     *
     * @return  array                       generated participants temp combined data
     ************************************************************************/
    public function getCombinedData() : array
    {
        return $this->combinedData;
    }
    /** **********************************************************************
     * clean temp data
     *
     * @return void
     ************************************************************************/
    public function clean() : void
    {
        $this->structure            = [];
        $this->dbStructure          = [];
        $this->classesStructure     = [];
        $this->providedData         = [];
        $this->providedMatchedData  = [];
        $this->providedXmlData      = [];
        $this->combinedData         = [];

        $this->dbStructureGenerator->clean();
        $this->classesStructureGenerator->clean();
        $this->providedDataGenerator->clean();
        $this->providedXmlDataGenerator->clean();
    }
}