<?php
declare(strict_types=1);

namespace Main\Helpers;

use
    RuntimeException,
    UnexpectedValueException,
    SplFileInfo,
    RecursiveIteratorIterator,
    RecursiveDirectoryIterator,
    Main\Singleton;
/** ***********************************************************************************************
 * Application config class
 * Provides access to system configs params
 *
 * @package exchange_helpers
 * @method  static Config getInstance
 * @author  Hvorostenko
 *************************************************************************************************/
class Config
{
    use Singleton;

    private
        $paramsFolder   = PARAMS_FOLDER,
        $params         = [];
    /** **********************************************************************
     * constructor
     ************************************************************************/
    private function __construct()
    {
        $paramsFiles    = $this->getAllParamsFiles();
        $logger         = Logger::getInstance();

        if (count($paramsFiles) <= 0)
        {
            $logger->addError('Config object: creating error, no params files was found');
        }

        foreach ($paramsFiles as $file)
        {
            $filePath       = $file->getPathname();
            $fileContent    = include $filePath;
            $libraryName    = $this->getLibraryName($filePath);

            if (is_array($fileContent))
            {
                foreach ($fileContent as $index => $value)
                {
                    $this->params[$libraryName.'.'.$index] = $value;
                }
            }
        }

        $logger->addNotice('Config object: successfully created');
    }
    /** **********************************************************************
     * get parameter value by name
     *
     * @param   string  $paramName          full parameter name
     * @return  string                      parameter value
     * @example                             $config->getParam('main.someImportantParameter')
     * @example                             $config->getParam('users.connectionAD.login')
     ************************************************************************/
    public function getParam(string $paramName) : string
    {
        if (array_key_exists($paramName, $this->params))
        {
            return $this->params[$paramName];
        }

        Logger::getInstance()->addWarning("Config object: trying to get unknown parameter \"$paramName\"");
        return '';
    }
    /** **********************************************************************
     * get all params files
     *
     * @return  SplFileInfo[]               files
     ************************************************************************/
    private function getAllParamsFiles() : array
    {
        try
        {
            $result     = [];
            $logger     = Logger::getInstance();
            $directory  = new RecursiveDirectoryIterator($this->paramsFolder);
            $iterator   = new RecursiveIteratorIterator($directory);

            while ($iterator->valid())
            {
                $file = $iterator->current();

                if ($file->isFile() && $file->getExtension() == 'php')
                {
                    if ($file->isReadable())
                    {
                        $result[] = $file;
                    }
                    else
                    {
                        $filePath = $file->getFilename();
                        $logger->addWarning("Config object: caught unreadable file \"$filePath\"");
                    }
                }

                $iterator->next();
            }

            return $result;
        }
        catch (UnexpectedValueException $exception)
        {
            return [];
        }
        catch (RuntimeException $exception)
        {
            return [];
        }
    }
    /** **********************************************************************
     * get params library name by params file path
     *
     * @param   string  $filePath           file path
     * @return  string                      library name
     ************************************************************************/
    private function getLibraryName(string $filePath) : string
    {
        $libraryName    = str_replace($this->paramsFolder.DS,   '',     $filePath);
        $libraryName    = str_replace('.php',                   '',     $libraryName);
        $libraryName    = str_replace(DS,                       '.',    $libraryName);

        return $libraryName;
    }
}