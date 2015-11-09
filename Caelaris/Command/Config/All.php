<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Caelaris\Command\Config;

use Caelaris\Command\Config;
use Caelaris\Lib\Cli;
use Caelaris\Toolkit;

/**
 * Class All
 */
class All extends Config
{
    public static $name = 'config:list';

    /**
     * @throws \Exception
     */
    protected static function executeCommand()
    {
        $configurations = static::getConfigurations();

        Cli::write('Available configurations', Cli::CLI_DEBUG);
        foreach ($configurations as $configuration) {
            Cli::write($configuration, Cli::CLI_SUCCESS, 1);
        }
        return;
    }

    /**
     * Return available configurations
     *
     * @return array
     * @throws \Exception
     */
    public static function getConfigurations()
    {
        $configurations = array();

        $path = array(
            TOOLKIT_BASE,
            Toolkit::CONF_DIR,
            Toolkit::CONF_DIR_EXTENSIONS
        );

        $directoryPath = implode(DIRECTORY_SEPARATOR, $path);
        $directory = new \RecursiveDirectoryIterator($directoryPath);
        /** @var \SplFileInfo $filename */
        foreach (new \RecursiveIteratorIterator($directory) as $filename)
        {
            /** Filter out directories */
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
            throw new \Exception('ERROR: No configurations found');
        }

        return $configurations;
    }
}