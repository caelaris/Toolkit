<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

if (!class_exists('Caelaris_Toolkit')) {
    Caelaris_Autoloader::register();
}

/**
 * Class Caelaris_Autoloader
 */
class Caelaris_Autoloader
{
    /**
     * Registered flag
     *
     * @var boolean
     */
    protected static $registered = false;

    /**
     * Library directory
     *
     * @var string
     */
    protected static $libDir;

    /**
     * Register the autoloader in the spl autoloader
     *
     * @return void
     * @throws Exception If there was an error in registration
     */
    public static function register(){
        if( self::$registered ){
            return;
        }

        self::$libDir = dirname(__FILE__);

        if(false === spl_autoload_register(array('Caelaris_Autoloader', 'loadClass'))){
            throw new Exception('ERROR: Unable to register Caelaris_Autoloader::loadClass as an autoloading method.');
        }

        self::$registered = true;
    }

    /**
     * Unregisters the autoloader
     *
     * @return void
     */
    public static function unregister(){
        spl_autoload_unregister(array('Caelaris_Autoloader', 'loadClass'));
        self::$registered = false;
    }

    /**
     * Loads the class
     *
     * @param string $className The class to load
     *
     * @return bool|void
     * @throws Exception
     */
    public static function loadClass($className)
    {
        /** Handle only toolkit classes */
        if(strpos($className, 'Caelaris_') !== 0){
            return;
        }

        $className = substr($className,9);
        $fileName = self::$libDir . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if(file_exists($fileName)){
            require $fileName;
        }
    }
}