<?php
declare(strict_types=1);

namespace Main\Helpers;

use
    DomainException,
    RuntimeException,
    UnexpectedValueException,
    InvalidArgumentException,
    SplFileInfo,
    RecursiveIteratorIterator,
    RecursiveDirectoryIterator;
/** ***********************************************************************************************
 * Application localization class
 * Provides methods for work with application localization
 *
 * @package exchange_helpers
 * @author  Hvorostenko
 *************************************************************************************************/
class Localization
{
    private
        $lang       = '',
        $messages   = [];
    /** **********************************************************************
     * constructor
     *
     * @param   string  $lang               language code
     * @throws  InvalidArgumentException    language code not exist
     * @throws  DomainException             need files resources unreachable
     ************************************************************************/
    public function __construct(string $lang)
    {
        $logger                     = Logger::getInstance();
        $localizationRootFolder     = Config::getInstance()->getParam('main.localizationFolder');
        $currentLocalizationFolder  = strlen($localizationRootFolder) > 0 && strlen($lang) > 0
            ? DOCUMENT_ROOT.DS.$localizationRootFolder.DS.$lang
            : '';
        $localizationFiles          = $this->getLocalizationFiles($currentLocalizationFolder);

        if (strlen($lang) <= 0)
        {
            $error = 'language argument empty';
            $logger->addWarning("Localization object creating failed: $error");
            throw new InvalidArgumentException($error);
        }
        if (strlen($localizationRootFolder) <= 0)
        {
            $error = 'localization folder param was not found';
            $logger->addWarning("Localization object creating failed: $error");
            throw new DomainException($error);
        }
        if (count($localizationFiles) <= 0)
        {
            $error = 'no localization files was found';
            $logger->addWarning("Localization object creating failed: $error");
            throw new DomainException($error);
        }

        foreach ($localizationFiles as $file)
        {
            $filePath       = $file->getPathname();
            $fileContent    = include $filePath;
            $libraryName    = $this->getLibraryName($filePath, $currentLocalizationFolder);

            if (is_array($fileContent))
            {
                foreach ($fileContent as $index => $value)
                {
                    $this->messages[$libraryName.'.'.$index] = $value;
                }
            }
        }

        $this->lang = $lang;
        $logger->addNotice("Localization object created successfully for \"$lang\" language");
    }
    /** **********************************************************************
     * get message
     *
     * @param   string  $message            need message full name
     * @return  string                      message value
     * @example                             $loc->getMessage('errors.someErrorType')
     * @example                             $loc->getMessage('main.important.someImportantMessage')
     ************************************************************************/
    public function getMessage(string $message) : string
    {
        if (array_key_exists($message, $this->messages))
        {
            return $this->messages[$message];
        }

        $lang = $this->lang;
        Logger::getInstance()->addWarning("Localization message \"$message\" for \"$lang\" language was not found");
        return '';
    }
    /** **********************************************************************
     * get all localization files in folder
     *
     * @param   string  $folderPath         inspecting folder path
     * @return  SplFileInfo[]               files
     ************************************************************************/
    private function getLocalizationFiles(string $folderPath) : array
    {
        if (strlen($folderPath) <= 0)
        {
            return [];
        }

        try
        {
            $result     = [];
            $directory  = new RecursiveDirectoryIterator($folderPath);
            $iterator   = new RecursiveIteratorIterator($directory);

            while ($iterator->valid())
            {
                $file = $iterator->current();
                if ($file->isFile() && $file->isReadable() && $file->getExtension() == 'php')
                {
                    $result[] = $file;
                }
                $iterator->next();
            }

            return $result;
        }
        catch (UnexpectedValueException|RuntimeException $exception)
        {
            return [];
        }
    }
    /** **********************************************************************
     * get localization library name by localization file path
     *
     * @param   string  $filePath           localization file path
     * @param   string  $localizationRoot   localization root path
     * @return  string                      localization library name
     ************************************************************************/
    private function getLibraryName(string $filePath, string $localizationRoot) : string
    {
        $libraryName    = str_replace($localizationRoot.DS, '',     $filePath);
        $libraryName    = str_replace('.php',               '',     $libraryName);
        $libraryName    = str_replace(DS,                   '.',    $libraryName);

        return $libraryName;
    }
}