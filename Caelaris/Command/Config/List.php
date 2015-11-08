<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

/**
 * Class Caelaris_Command_Config_List
 */
class Caelaris_Command_Config_List extends Caelaris_Command_Config
{
    public static $name = 'config:list';

    /**
     * @throws Exception
     */
    protected static function executeCommand()
    {
        $configurations = static::getConfigurations();

        Caelaris_Lib_Cli::write('Available configurations', Caelaris_Lib_Cli::CLI_DEBUG);
        foreach ($configurations as $configuration) {
            Caelaris_Lib_Cli::write($configuration, Caelaris_Lib_Cli::CLI_SUCCESS, 1);
        }
        return;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getConfigurations()
    {
        $configurations = array();
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(TOOLKIT_BASE . DIRECTORY_SEPARATOR .'conf' . DIRECTORY_SEPARATOR . 'extensions')) as $filename)
        {
            // filter out "." and ".."
            if ($filename->isDir()) {
                continue;
            }

            $filename = $filename->__toString();
            $configurations[] = basename($filename, '.json');
        }

        if (empty($configurations)) {
            throw new Exception('ERROR: No configurations found');
        }

        return $configurations;
    }
}