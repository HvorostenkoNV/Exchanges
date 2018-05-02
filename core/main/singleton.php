<?php
declare(strict_types=1);

namespace Main;
/** ***********************************************************************************************
 * Singleton trait, provides singleton functional
 *
 * @package exchange_main
 * @author  Hvorostenko
 *************************************************************************************************/
trait Singleton
{
    private static $instance = [];
    /** **********************************************************************
     * singleton constructor
     ************************************************************************/
    public static function getInstance()
    {
        $currentClass = static::class;

        if (!array_key_exists($currentClass, self::$instance))
        {
            self::$instance[$currentClass] = new static;
        }

        return self::$instance[$currentClass];
    }
    /** closed constructor */
    private function __construct()  {}
    private function __clone()      {}
}