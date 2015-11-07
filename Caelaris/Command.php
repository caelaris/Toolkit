<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

/**
 * Class Caelaris_Command
 */
class Caelaris_Command implements Caelaris_Command_Interface
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
        if (Caelaris_Toolkit::isHelpMode()) {
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
        Caelaris_Lib_Cli::write(static::$name . ' help will be written at a later point', Caelaris_Lib_Cli::CLI_ERROR);
    }
}