<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
	Main\Exchange\Participants\Data\ProvidedData,
	Main\Exchange\Participants\Data\FieldsParams,
	Main\Exchange\Participants\Data\MatchingRules,
	Main\Exchange\Participants\Data\CombiningRules,
	Main\Exchange\Participants\Data\DeliveredData;
/** ***********************************************************************************************
 * Participant abstract class
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class AbstractParticipants implements Participant
{
	/** **********************************************************************
	 * get provided data
	 * @return  ProvidedData   provided data
	 * TODO
	 ************************************************************************/
	final public function getProvidedData() : ProvidedData
	{
		return new ProvidedData;
	}
	/** **********************************************************************
	 * get fields params
	 * @return  FieldsParams   fields params
	 * TODO
	 ************************************************************************/
	final public function getFieldsParams() : FieldsParams
	{
		return new \Main\Exchange\Participants\Data\Users1C\FieldsParams;
	}
	/** **********************************************************************
	 * get matching rules
	 * @return  MatchingRules   matching rules
	 * TODO
	 ************************************************************************/
	final public function getMatchingRules() : MatchingRules
	{
		return new \Main\Exchange\Participants\Data\Users1C\MatchingRules;
	}
	/** **********************************************************************
	 * get combining rules
	 * @return  CombiningRules   combining rules
	 * TODO
	 ************************************************************************/
	final public function getCombiningRules() : CombiningRules
	{
		return new \Main\Exchange\Participants\Data\Users1C\CombiningRules;
	}
	/** **********************************************************************
	 * provide data
	 * @param   DeliveredData   $data   provided data
	 * @return  bool                    providing data result
	 * TODO
	 ************************************************************************/
	final public function provideData(DeliveredData $data) : bool
	{
		return false;
	}
	/** **********************************************************************
	 * read xml file
	 * @param   string  $path   xml file path
	 * @return  ProvidedData    data
	 * TODO
	 ************************************************************************/
	protected function readXml(string $path) : ProvidedData
	{
		return new ProvidedData;
	}
	/** **********************************************************************
	 * read provided data and get it
	 * @return  ProvidedData    data
	 ************************************************************************/
	abstract protected function readProvidedData() : ProvidedData;
	/** **********************************************************************
	 * write delivered data
	 * @param   DeliveredData   $data   data to write
	 * @return  bool                    process result
	 ************************************************************************/
	abstract protected function writeDeliveredData(DeliveredData $data) : bool;
}