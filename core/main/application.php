<?php
declare(strict_types=1);

namespace Main;

use
	Main\Helpers\Logger,
	Main\Exchange\Exchange,
	Main\Administration\Administration,
	Main\API\API;
/**************************************************************************************************
 * Application class, application entrance point
 * @package exchange_main
 * @method  static Application getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class Application
{
	use Singleton;
	/** **********************************************************************
	 * constructor
	 ************************************************************************/
	private function __construct()
	{
		Logger::getInstance()->addNotice('Application object created');
	}
	/** **********************************************************************
	 * destructor
	 ************************************************************************/
	public function __destruct()
	{
		Logger::getInstance()->write();
	}
	/** **********************************************************************
	 * get exchange object
	 ************************************************************************/
	public function getExchange() : Exchange
	{
		return Exchange::getInstance();
	}
	/** **********************************************************************
	 * get administration object
	 ************************************************************************/
	public function getAdministration() : Administration
	{
		return Administration::getInstance();
	}
	/** **********************************************************************
	 * get API object
	 ************************************************************************/
	public function getAPI() : API
	{
		return API::getInstance();
	}
}