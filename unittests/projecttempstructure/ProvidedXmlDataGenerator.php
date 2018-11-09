<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use RuntimeException;
/** ***********************************************************************************************
 * Class for creating project temp provided xml data
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ProvidedXmlDataGenerator
{
    private $xmlGenerator = null;
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        $this->xmlGenerator = new XmlGenerator;
    }
    /** **********************************************************************
     * generate provided XML data files
     *
     * @param   array $data                 generated provided data
     * @return  array                       generated provided XML data files
     * @throws  RuntimeException            creating error
     ************************************************************************/
    public function generate(array $data) : array
    {
        $result = [];

        foreach ($data as $procedureCode => $participantsData)
        {
            $result[$procedureCode] = [];
            foreach ($participantsData as $participantCode => $data)
            {
                $result[$procedureCode][$participantCode] = $this->xmlGenerator->createXml($data);
            }
        }

        return $result;
    }
    /** **********************************************************************
     * clean temp provided XML data files
     *
     * @return void
     ************************************************************************/
    public function clean() : void
    {
        $this->xmlGenerator->clean();
    }
}