<?php
namespace Main\Exchange\Participants;

use
	Main\Exchange\Participants\Data\
	{
		ProvidedData,
		DeliveredData,
		FieldsParams,
		MatchingRules,
		CombiningRules
	};

interface Participant
{
	public function getProvidedData() : ProvidedData;
	public function getFieldsParams() : FieldsParams;
	public function getMatchingRules() : MatchingRules;
	public function getCombiningRules() : CombiningRules;
	public function provideData(ProvidedData $data) : boolean;

	public function readProvidedData() : ProvidedData;
	public function writeDeliveredData(DeliveredData $data) : boolean;
}