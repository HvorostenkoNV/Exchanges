<?php
namespace Main;

use
	Main\
	{
		Exchange\Exchange,
		Administration\Administration,
		API\API
	};

class Application
{
	use Singltone;

	public function getExchange() : Exchange
	{
		return Exchange::getInstance();
	}

	public function getAdministration() : Administration
	{
		return Administration::getInstance();
	}

	public function getAPI() : API
	{
		return API::getInstance();
	}
}