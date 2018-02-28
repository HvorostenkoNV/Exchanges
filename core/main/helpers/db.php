<?php
declare(strict_types=1);

namespace Main\Helpers;

use
	RuntimeException,
	PDOException,
	PDO,
	Iterator,
	SplQueue,
	Main\Singleton;
/**************************************************************************************************
 * DB class, provides methods to work with db
 * @package exchange_helpers
 * @method  static DB getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class DB
{
	use Singleton;

	private
		$pdo                = NULL,
		$preparedQueries    = [],
		$lastError          = '';
	/** **********************************************************************
	 * constructor
	 ************************************************************************/
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
			$this->pdo = new PDO
			(
				'mysql:dbname='.$dbName.';host='.$dbHost,
				$dbLogin,
				$dbPassword,
				[
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
				]
			);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch( PDOException $exception )
		{
			throw new RuntimeException($exception->getMessage());
		}

		Logger::getInstance()->addNotice('DB object created, connection success');
	}
	/** **********************************************************************
	 * query
	 * @param   string  $sqlQuery   sql query string
	 * @param   array   $params     query params for preparing
	 * @return  Iterator            query result in rows
	 ************************************************************************/
	public function query(string $sqlQuery, array $params = []) : Iterator
	{
		$preparedQuery  = NULL;
		$result         = new SplQueue;

		$this->lastError = '';
		if( array_key_exists($sqlQuery, $this->preparedQueries) )
			$preparedQuery = $this->preparedQueries[$sqlQuery];
		else
		{
			try
			{
				$this->preparedQueries[$sqlQuery] = $preparedQuery = $this->pdo->prepare($sqlQuery);
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
				foreach( $preparedQuery->fetchAll(PDO::FETCH_OBJ) as $rowObject )
					$result->push($rowObject);
			}
			catch( PDOException $exception )
			{
				$this->lastError = $exception->getMessage();
			}
		}

		return $result;
	}
	/** **********************************************************************
	 * save item
	 * @param   string  $sqlQuery   sql query string
	 * @param   array   $params     query params for preparing
	 * @return  int                 created item id
	 ************************************************************************/
	public function save(string $sqlQuery, array $params = []) : int
	{
		$this->query($sqlQuery, $params);
		return intval($this->pdo->lastInsertId());
	}
	/** **********************************************************************
	 * delete item
	 * @param   string  $sqlQuery   sql query string
	 * @param   array   $params     query params for preparing
	 * @return  bool                deleting result
	 * TODO
	 ************************************************************************/
	public function delete(string $sqlQuery, array $params = []) : bool
	{
		return false;
	}
	/** **********************************************************************
	 * check if has last error
	 * @return  bool
	 ************************************************************************/
	public function hasLastError() : bool
	{
		return strlen($this->lastError) > 0;
	}
	/** **********************************************************************
	 * get last error message
	 * @return  string
	 ************************************************************************/
	public function getLastError() : string
	{
		return $this->lastError;
	}
}