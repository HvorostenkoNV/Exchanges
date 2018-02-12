<?php
namespace Main\Exchange\Procedures;

class Manager
{
	public static function getProcedure(string $procedureName) : ?Procedure
	{
		return NULL;
	}

	public static function getProceduresList() : array
	{
		return [];
	}

	public static function addProcedure(Params $params) : boolean
	{
		return false;
	}

	public static function deleteProcedure(string $procedureName) : boolean
	{
		return false;
	}

	public static function changeProcedure(string $procedureName, Params $params) : boolean
	{
		return false;
	}
}