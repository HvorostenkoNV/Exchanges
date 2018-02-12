<?php
namespace Main\Exchange\Procedures;

class Params
{
	private
		$name           = '',
		$fieldsMatching = [];

	public function __construct(array $data)
	{

	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getFieldsMatching() : array
	{
		return $this->fieldsMatching;
	}
}