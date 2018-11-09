<?php
declare(strict_types=1);

use Main\Exchange\Exchange;
/** ***********************************************************************************************
 * exchange entrance file
 *
 * @package exchange_public
 *************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';

Exchange::getInstance()->run();