<?php
namespace Main\Helpers;

use
	RuntimeException,
	PDOException,
	PDO,
	Main\Singltone;

class DB
{
	use Singltone;

	private
		$PDO                = NULL,
		$preparedQueries    = [],
		$lastError          = '';
	/* -------------------------------------------------------------------- */
	/* ---------------------------- construct ----------------------------- */
	/* -------------------------------------------------------------------- */
	private function __construct()
	{
		$config     = Config::getInstance();
		$dbName     = $config->getParam('db.name');
		$dbLogin    = $config->getParam('db.login');
		$dbPassword = $config->getParam('db.password');
		$dbHost     = $config->getParam('db.host');

		if( !extension_loaded('PDO') )
			throw new RuntimeException('PHP PDO extension unavailable');

		if( strlen($dbName) <= 0 || strlen($dbLogin) <= 0 || strlen($dbPassword) <= 0 || strlen($dbHost) <= 0 )
			throw new RuntimeException('DB connection params are not complete');

		try
		{
			$this->PDO = new PDO
			(
				'mysql:dbname='.$dbName.';host='.$dbHost,
				$dbLogin,
				$dbPassword,
				[
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
				]
			);
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch( PDOException $exception )
		{
			throw new RuntimeException($exception->getMessage());
		}

		Logger::getInstance()->addNotice('DB object created, connection success');
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------------ query ------------------------------- */
	/* -------------------------------------------------------------------- */
	public function query(string $sqlQuery, array $params) : array
	{
		$preparedQuery  = NULL;
		$result         = [];

		$this->lastError = '';
		if( array_key_exists($sqlQuery, $this->preparedQueries) )
			$preparedQuery = $this->preparedQueries[$sqlQuery];
		else
		{
			try
			{
				$this->preparedQueries[$sqlQuery] = $preparedQuery = $this->PDO->prepare($sqlQuery);
			}
			catch( PDOException $exception )
			{
				$this->lastError = $exception->getMessage();
			}
		}

		if( $preparedQuery )
		{
			try
			{
				$preparedQuery->execute($params);
				$result = $preparedQuery->fetchAll(PDO::FETCH_ASSOC);
			}
			catch( PDOException $exception )
			{
				$this->lastError = $exception->getMessage();
			}
		}

		return $result;
	}
	/* -------------------------------------------------------------------- */
	/* ------------------------------ errors ------------------------------ */
	/* -------------------------------------------------------------------- */
	public function hasLastError() : bool
	{
		return strlen($this->lastError) > 0;
	}
	public function getLastError() : string
	{
		return $this->lastError;
	}
	// TODO
}