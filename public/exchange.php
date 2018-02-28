<?php
declare(strict_types=1);

use Main\Application;
/** ***********************************************************************************************
 * exchange entrance file
 * @package exchange_main
 *************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';

Application::getInstance()->getExchange()->run();