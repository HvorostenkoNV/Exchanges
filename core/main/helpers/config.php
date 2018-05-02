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

    private $params = [];
    /** **********************************************************************
     * constructor
     * IMPORTANT: application shut down on any config constructing error
     ************************************************************************/
    private function __construct()
    {
        $paramsFiles    = $this->getParamsFiles(PARAMS_FOLDER);
        $logger         = Logger::getInstance();

        if (count($paramsFiles) <= 0)
        {
            $logger->addError('Config object creating error: no params files was found');
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

        $logger->addNotice('Config object created');
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
        else
        {
            Logger::getInstance()->addWarning("Param \"$paramName\" was not found");
            return '';
        }
    }
    /** **********************************************************************
     * get all params files
     *
     * @param   string  $folderPath         inspecting folder path
     * @return  SplFileInfo[]               files
     ************************************************************************/
    private function getParamsFiles(string $folderPath) : array
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
     * get params library name by params file path
     *
     * @param   string  $filePath           file path
     * @return  string                      library name
     ************************************************************************/
    private function getLibraryName(string $filePath) : string
    {
        $libraryName    = str_replace(PARAMS_FOLDER.DS, '',     $filePath);
        $libraryName    = str_replace('.php',           '',     $libraryName);
        $libraryName    = str_replace(DS,               '.',    $libraryName);

        return $libraryName;
    }
}