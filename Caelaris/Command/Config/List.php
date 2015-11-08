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
     * Return available configurations
     *
     * @return array
     * @throws Exception
     */
    public static function getConfigurations()
    {
        $configurations = array();

        $path = array(
            TOOLKIT_BASE,
            Caelaris_Toolkit::CONF_DIR,
            Caelaris_Toolkit::CONF_DIR_EXTENSIONS
        );

        $directoryPath = implode(DIRECTORY_SEPARATOR, $path);
        $directory = new RecursiveDirectoryIterator($directoryPath);
        /** @var SplFileInfo $filename */
        foreach (new RecursiveIteratorIterator($directory) as $filename)
        {
            // filter out "." and ".."
            if ($filename->isDir()) {
                continue;
            }

            /** Only allow json files */
            if ($filename->getExtension() != 'json') {
                continue;
            }

            $configurations[] = $filename->getBasename('.json');
            /**
             * @todo add validation to configurations
             */
        }

        if (empty($configurations)) {
            /** If no configuration files are found, error out */
            throw new Exception('ERROR: No configurations found');
        }

        return $configurations;
    }
}