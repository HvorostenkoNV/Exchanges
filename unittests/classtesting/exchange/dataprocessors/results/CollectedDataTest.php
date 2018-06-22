<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors\Result;

use
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\DataProcessors\Results\CollectedData;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Result\CollectedData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class CollectedDataTest extends MapDataAbstractTest
{
    /** @var TempStructureGenerator */
    private static $structureGenerator = null;
    /** **********************************************************************
     * construct
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$structureGenerator = new TempStructureGenerator;
        self::$structureGenerator->generate();
    }
    /** **********************************************************************
     * destruct
     ************************************************************************/
    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();

        self::$structureGenerator->clean();
    }
    /** **********************************************************************
     * get Map class name
     *
     * @return  string                      Map class name
     ************************************************************************/
    public static function getMapClassName() : string
    {
        return CollectedData::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectData() : array
    {
        $result                 = [];
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
            {
                $participantClassName = $tempClassesStructure[$procedureCode]['participants'][$participantCode]['class'];
                $result[] = [new $participantClassName, new ProvidedData];
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect keys
     *
     * @return  array                       incorrect data keys
     ************************************************************************/
    public static function getIncorrectDataKeys() : array
    {
        return
        [
            'string',
            '',
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new CollectedData,
            new ProvidedData,
            null
        ];
    }
    /** **********************************************************************
     * get incorrect values
     *
     * @return  array                       incorrect data values
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return
        [
            'string',
            '',
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new CollectedData,
            null
        ];
    }
}