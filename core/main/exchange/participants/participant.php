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
 * Participants interface
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
interface Participant
{
	/** **********************************************************************
	 * get provided data
	 * @return  ProvidedData   provided data
	 ************************************************************************/
	public function getProvidedData() : ProvidedData;
	/** **********************************************************************
	 * get fields params
	 * @return  FieldsParams   fields params
	 ************************************************************************/
	public function getFieldsParams() : FieldsParams;
	/** **********************************************************************
	 * get matching rules
	 * @return  MatchingRules   matching rules
	 ************************************************************************/
	public function getMatchingRules() : MatchingRules;
	/** **********************************************************************
	 * get combining rules
	 * @return  CombiningRules   combining rules
	 ************************************************************************/
	public function getCombiningRules() : CombiningRules;
	/** **********************************************************************
	 * provide data
	 * @param   DeliveredData   $data   provided data
	 * @return  bool                    providing data result
	 ************************************************************************/
	public function provideData(DeliveredData $data) : bool;
}