<?php
spl_autoload_register(function($className)
{
	$classesFolder      = 'core';
	$classNameExplode   = explode('\\', $className);
	$classFilePathArray = array_merge([$classesFolder], $classNameExplode);
	$classFilePath      = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.strtolower(implode(DIRECTORY_SEPARATOR, $classFilePathArray)).'.php';

	include $classFilePath;
});