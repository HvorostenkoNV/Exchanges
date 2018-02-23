<?php
use Main\Helpers\Logger;

$dirPathExplode = explode(DIRECTORY_SEPARATOR, __DIR__);
unset($dirPathExplode[count($dirPathExplode) - 1]);

define('DOCUMENT_ROOT', implode(DIRECTORY_SEPARATOR, $dirPathExplode));
define('DS',            DIRECTORY_SEPARATOR);
define('PARAMS_FOLDER', DOCUMENT_ROOT.DS.'params');
define('LOGS_FOLDER',   DOCUMENT_ROOT.DS.'logs');

spl_autoload_register(function($className)
{
	$className          = strtolower($className);
	$classesFolder      = 'core';
	$classNameExplode   = explode('\\', $className);
	$classFilePathArray = array_merge([$classesFolder], $classNameExplode);
	$classFilePath      = DOCUMENT_ROOT.DS.implode(DS, $classFilePathArray).'.php';
	$file               = new SplFileInfo($classFilePath);

	if( $file->isFile() && $file->getExtension() == 'php' )
		include $classFilePath;
	else
		Logger::getInstance()->addWarning('Trying to load unfounded class "'.$className.'"');
});