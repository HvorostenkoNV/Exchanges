<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors\Data;

use
    ReflectionClass,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    UnitTests\ClassTesting\Data\MapDataAbstractTest,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\DataProcessors\Data\MatchedItem;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Data\MatchedItem class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MatchedItemTest extends MapDataAbstractTest
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
        return MatchedItem::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectData() : array
    {
        $result = [];

        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantInfo)
            {
                $participant    = self::createParticipant($participantInfo['name']);
                $itemData       = new ItemData;
                $result[]       = [$participant, $itemData];
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
            new ItemData,
            new TempStructureGenerator,
            new MatchedItem,
            new MatchedItemTest,
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
            new TempStructureGenerator,
            new MatchedItem,
            new MatchedItemTest,
            null
        ];
    }
    /** **********************************************************************
     * create participant by name
     *
     * @param   string  $participantName    participant name
     * @return  Participant                 participant
     ************************************************************************/
    private static function createParticipant(string $participantName) : Participant
    {
        $reflection     = new ReflectionClass(Participant::class);
        $namespace      = $reflection->getNamespaceName();
        $qualifiedName  = $namespace.'\\'.$participantName;

        return new $qualifiedName;
    }
}