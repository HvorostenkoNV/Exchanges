<?php
namespace Main\Exchange\Participants;

use Main\Exchange\Participants\Data\
	{
		ProvidedData,
		DeliveredData,
		FieldsParams,
		MatchingRules,
		CombiningRules
	};

abstract class AbstractParticipants implements Participant
{
	final public function getProvidedData() : ProvidedData
	{
		// TODO
	}

	final public function getFieldsParams() : FieldsParams
	{
		// TODO
	}

	final public function getMatchingRules() : MatchingRules
	{
		// TODO
	}

	final public function getCombiningRules() : CombiningRules
	{
		// TODO
	}

	final public function provideData(ProvidedData $data) : bool
	{
		// TODO
	}

	protected function readXml(string $path) : ProvidedData
	{
		// TODO
	}

	protected function readBD(array $connectionParams) : ProvidedData
	{
		// TODO
	}

	abstract protected function readProvidedData() : ProvidedData;
	abstract protected function writeDeliveredData(DeliveredData $data) : bool;
}