<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Caelaris;

use Caelaris\Lib\Cli;

/**
 * Class Command
 */
class Command implements CommandInterface
{
    public static $name;

    /**
     * Actual command logic
     */
    protected static function executeCommand()
    {
    }

    /**
     * Check for help mode and execute command
     */
    public static function execute()
    {
        if (Toolkit::isHelpMode()) {
            static::help();
            return;
        }

        static::executeCommand();
        return;
    }

    /**
     * Help info for the current command
     */
    public static function help()
    {
        Cli::write(static::$name . ' help will be written at a later point', Cli::CLI_ERROR);
    }
}