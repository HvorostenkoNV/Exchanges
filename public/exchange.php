<?php
use Main\Application;

require $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'include.php';

Application::getInstance()->getExchange()->run();