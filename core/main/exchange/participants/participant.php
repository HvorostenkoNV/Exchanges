<?php
namespace Main\Exchange\Participants;

use Main\Exchange\Participants\Data\
	{
		ProvidedData,
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
	public function provideData(ProvidedData $data) : bool;
}