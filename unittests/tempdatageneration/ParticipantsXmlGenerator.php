<?php
declare(strict_types=1);

namespace UnitTests\TempDataGeneration;

use SplFileInfo;
/** ***********************************************************************************************
 * Class for creating project participants temp provided XML files
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ParticipantsXmlGenerator
{
    /** **********************************************************************
     * constructor
     *
     * @param   ParticipantsDataGenerator $participantsDataGenerator    structure generator
     ************************************************************************/
    public function __construct(ParticipantsDataGenerator $participantsDataGenerator)
    {

    }
    /** **********************************************************************
     * generate project temp files structure
     *
     * @return void
     ************************************************************************/
    public function generate() : void
    {

    }
    /** **********************************************************************
     * get procedure participant temp provided XML file
     *
     * @param   string  $procedureCode                                  procedure code
     * @param   string  $participantCode                                participant code
     * @return  SplFileInfo                                             participant temp provided XML file
     ************************************************************************/
    public function getXml(string $procedureCode, string $participantCode) : SplFileInfo
    {
        return new SplFileInfo('');
    }
    /** **********************************************************************
     * clear temp files structure
     *
     * @return void
     ************************************************************************/
    public function clear() : void
    {

    }
}