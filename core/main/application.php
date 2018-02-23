<?php
namespace Main;

use
	Main\Helpers\Logger,
	Main\Exchange\Exchange,
	Main\Administration\Administration,
	Main\API\API;

class Application
{
	use Singltone;
	/* -------------------------------------------------------------------- */
	/* ------------------------ construct/destruct ------------------------ */
	/* -------------------------------------------------------------------- */
	private function __construct()
	{
		Logger::getInstance()->addNotice('Application object created');
	}

	public function __destruct()
	{
		Logger::getInstance()->write();
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------ functional access ------------------------- */
	/* -------------------------------------------------------------------- */
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