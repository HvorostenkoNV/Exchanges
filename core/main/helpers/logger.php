<?php
declare(strict_types=1);

namespace Main\Helpers;

use
    DateTime,
    DateInterval,
    SplFileInfo,
    Main\Singleton;
/** ***********************************************************************************************
 * Logger class, provides logging functional
 *
 * @package exchange_helpers
 * @method  static Logger getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class Logger
{
    use Singleton;

    private static $messagesTypes =
        [
            'note'      =>
                [
                    'weight'    => 0,
                    'critical'  => false
                ],
            'warning'   =>
                [
                    'weight'    => 3,
                    'critical'  => false
                ],
            'error'     =>
                [
                    'weight'    => 5,
                    'critical'  => true
                ]
        ];
    private $messages = [];
    /** **********************************************************************
     * constructor
     ************************************************************************/
    private function __construct()
    {
        $this->addNotice('Logger object: successfully created');
    }
    /** **********************************************************************
     * add notice into log file
     *
     * @param   string  $message            message text
     ************************************************************************/
    public function addNotice(string $message) : void
    {
        $this->addString('note', $message);
    }
    /** **********************************************************************
     * add warning into log file
     *
     * @param   string  $message            message text
     ************************************************************************/
    public function addWarning(string $message) : void
    {
        $this->addString('warning', $message);
    }
    /** **********************************************************************
     * add error into log file
     *
     * @param   string  $message            message text
     ************************************************************************/
    public function addError(string $message) : void
    {
        $this->addString('error', $message);
    }
    /** **********************************************************************
     * saving message string
     * IMPORTANT: message with critical type will shut system down
     *
     * @param   string  $type               message type
     * @param   string  $message            message text
     ************************************************************************/
    private function addString(string $type, string $message) : void
    {
        if (!array_key_exists($type, self::$messagesTypes) || strlen($message) <= 0)
        {
            return;
        }

        $this->messages[] =
            [
                'type'      => $type,
                'message'   => $message
            ];

        if (self::$messagesTypes[$type]['critical'])
        {
            $this->write();
            trigger_error($message, E_USER_ERROR);
        }
    }
    /** **********************************************************************
     * saving log file
     ************************************************************************/
    public function write() : void
    {
        $date           = new DateTime;
        $config         = Config::getInstance();
        $logsFolderPath = DOCUMENT_ROOT.DIRECTORY_SEPARATOR.$config->getParam('structure.logsFolder');
        $logsFolder     = new SplFileInfo($logsFolderPath);
        $messages       = [];

        if (!$logsFolder->isDir() || !$logsFolder->isWritable())
        {
            return;
        }

        foreach ($this->messages as $messageInfo)
        {
            $message        = $messageInfo['message'];
            $messageWeight  = self::$messagesTypes[$messageInfo['type']]['weight'];

            switch ($messageWeight)
            {
                case 0:
                    $messages[] = "#NOTICE# $message";
                    break;
                case 3:
                    $messages[] = "#WARNING# $message";
                    break;
                case 5:
                    $messages[] = "#FATAL ERROR# $message";
                    break;
            }
        }

        while (true)
        {
            $logFileName    = $logsFolder->getPathname().DIRECTORY_SEPARATOR.$date->format('Y-m-d_H-i-s').'.txt';
            $logFile        = new SplFileInfo($logFileName);

            if (!$logFile->isFile())
            {
                $logFile
                    ->openFile('w')
                    ->fwrite(implode("\n", $messages));
                break;
            }

            $date->add(new DateInterval('PT1S'));
        }
    }
}