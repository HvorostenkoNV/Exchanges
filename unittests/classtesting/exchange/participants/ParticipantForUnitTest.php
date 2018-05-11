<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    SplFileInfo,
    Main\Exchange\Participants\AbstractParticipants,
    Main\Exchange\Participants\Data\Data,
    Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Application participant Users1C
 *
 * @property    SplFileInfo $tempXmlFromUnitTest
 * @package     exchange_unit_tests
 * @author      Hvorostenko
 *************************************************************************************************/
class ParticipantForUnitTest extends AbstractParticipants
{
    /** @var SplFileInfo */
    public $tempXmlFromUnitTest     = null;
    /** @var SplFileInfo */
    public $createdTempXmlAnswer    = null;
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  Data                        data
     ************************************************************************/
    protected function readProvidedData() : Data
    {
        return $this->tempXmlFromUnitTest
            ? $this->readXml($this->tempXmlFromUnitTest)
            : new ProvidedData;
    }
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   Data    $data               data to write
     * @return  bool                        process result
     ************************************************************************/
    protected function provideDataForDelivery(Data $data) : bool
    {
        if ($this->createdTempXmlAnswer)
        {
            return $this->writeXml($data, $this->createdTempXmlAnswer);
        }

        return false;
    }
}