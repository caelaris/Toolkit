<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

/**
 * Class Caelaris_Toolkit
 */
class Caelaris_Toolkit
{
    protected static $commandList = array();

    public static $command;
    public static $options;
    public static $arguments;
    public static $mode;
    const CONF_DIR = 'conf';
    const CONF_COMMAND_FILENAME = 'commands.json';

    /** @var  Caelaris_Config */
    public static $activeConfig;

    const MODE_VERBOSE = 'verbose';
    const MODE_HELP = 'help';

    public static function init()
    {
        define('TOOLKIT_BASE', dirname(__FILE__));
        self::parseOptions();

        self::loadCommands();
        self::parseArguments();
    }

    /**
     * Application entry point, initialize application and execute command
     *
     * @throws Exception
     */
    public static function run()
    {
        try {
            self::init();

            if (!self::$command) {
                self::manPage();
            }

            self::executeCommand();
        } catch (Exception $e) {
            Caelaris_Lib_Cli::write($e->getMessage(), Caelaris_Lib_Cli::CLI_ERROR);
        }
    }

    /**
     * Parse all arguments into command and it's arguments
     */
    protected static function parseArguments()
    {
        $arguments = $_SERVER['argv'];
        foreach ($arguments as $argument) {
            /** All arguments after the command are added to the argument list  */
            if (self::$command) {
                self::$arguments[] = $argument;
            }

            /** First recognisable argument is set as command */
            if (isset(self::$commandList[$argument])) {
                self::$command = $argument;
            }
        }

    }

    /**
     * Parse options and set the application mode
     */
    protected static function parseOptions()
    {
        $options = getopt('hv', array('help','verbose'));
        self::$options = $options;

        if (isset($options['h']) || isset($options['help'])) {
            self::$mode = self::MODE_HELP;
        } elseif (isset($options['v']) || isset($options['verbose'])) {
            self::$mode = self::MODE_VERBOSE;
        }
    }

    /**
     * Load all commands from the configuration
     *
     * @throws Exception
     */
    public static function loadCommands()
    {
        if (!defined('TOOLKIT_BASE')) {
            /** TOOLKIT_BASE needs to be defined to locate configuration */
            throw new Exception('ERROR: TOOLKIT_BASE not defined');
        }

        $filePath = TOOLKIT_BASE . DIRECTORY_SEPARATOR . self::CONF_DIR . DIRECTORY_SEPARATOR . self::CONF_COMMAND_FILENAME;

        if (!file_exists($filePath)) {
            /** If there is no command configuration file, the application cannot run */
            throw new Exception('ERROR: cannot load command list');
        }

        $commands = json_decode(file_get_contents($filePath), true);

        if (empty($commands)) {
            /** If the command configuration file is empty or not valid JSON, the application cannot run */
            throw new Exception('ERROR: command conf file is not correct json');
        }

        /** Loop through configured commands and check them for validity */
        foreach ($commands as $command => $className) {
            if (self::isValidCommandClass($className)) {
                self::$commandList[$command] = $className;
            }
        }

        if (empty(self::$commandList)) {
            /** If no valid commands are found, the application cannot run */
            throw new Exception('ERROR: no commands configured');
        }
    }

    /**
     * Check if $className is a valid command class
     *
     * @param string $className
     *
     * @return bool
     */
    public static function isValidCommandClass($className)
    {
        if (!class_exists($className)) {
            /** If the class does not exist, it is obviously not valid */
            Caelaris_Lib_Cli::writeVerbose('ERROR: command class does not exist : ' . $className);
            return false;
        }

        $interfaces = class_implements($className);
        if (!in_array('Caelaris_Command_Interface', $interfaces)) {
            /** If the class does not extend Caelaris_Command_Interface it is not a valid command */
            Caelaris_Lib_Cli::writeVerbose('ERROR: ' . $className . ' is not a valid command class');
            return false;
        }

        return true;
    }

    /**
     * If the config is not set yet, try to load it
     *
     * @return Caelaris_Config
     * @throws Exception
     */
    public static function getConfig()
    {
        if (!self::$activeConfig) {
            /** Get all extension configurations */
            $configurations = Caelaris_Command_Config_List::getConfigurations();
            if (!in_array(self::$arguments[0], $configurations)) {
                /** If the first argument is not a valid extension configuration, error out */
                throw new Exception('ERROR: No valid configuration argument passed');
            }

            /** Load configuration object with data from conf file */
            self::$activeConfig = new Caelaris_Config(self::$arguments[0]);
        }

        return self::$activeConfig;
    }

    /**
     * Returns if the application is in help mode
     *
     * @return bool
     */
    public static function isHelpMode()
    {
        return (self::$mode == self::MODE_HELP);
    }

    /**
     * Returns if the application is in verbose mode
     *
     * @return bool
     */
    public static function isVerboseMode()
    {
        return (self::$mode == self::MODE_VERBOSE);
    }

    /**
     * Find the correct command class and execute it
     *
     * @throws Exception
     */
    public static function executeCommand()
    {
        $commandClassName = self::$commandList[self::$command];

        /** @var Caelaris_Command $commandClassName */
        $commandClassName::execute();
    }

    /**
     * Write out help info
     */
    public static function manPage()
    {
        self::writeSignature();

        /** Write usage information */
        Caelaris_Lib_Cli::write('Usage:', Caelaris_Lib_Cli::CLI_DEBUG);
        Caelaris_Lib_Cli::write('command [options] [arguments]', false, 1);
        Caelaris_Lib_Cli::write('Options', Caelaris_Lib_Cli::CLI_DEBUG);
        Caelaris_Lib_Cli::write(' --help (-h)', Caelaris_Lib_Cli::CLI_SUCCESS, 1);
        Caelaris_Lib_Cli::write(' --verbose (-v)', Caelaris_Lib_Cli::CLI_SUCCESS, 1);

        /** Write available default commands */
        Caelaris_Lib_Cli::write('Default commands:', Caelaris_Lib_Cli::CLI_DEBUG);
        Caelaris_Lib_Cli::write('help', Caelaris_Lib_Cli::CLI_SUCCESS, 1);

        /** Write available custom commands */
        Caelaris_Lib_Cli::write('Custom commands:', Caelaris_Lib_Cli::CLI_DEBUG);
        foreach (self::$commandList as $command => $class) {
            Caelaris_Lib_Cli::write($command, Caelaris_Lib_Cli::CLI_SUCCESS, 1);
        }

        /** Exit script execution after man page is written out */
        exit;
    }

    /**
     * Write the signature for the toolkit
     */
    public static function writeSignature()
    {
        Caelaris_Lib_Cli::write('  _____         _   _   _ _   ', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write(' |_   _|__  ___| | | |_(_) |_ ', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write('   | |/ _ \/ _ \ | | / / |  _|', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write('   |_|\___/\___/_| |_\_\_|\__|', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write('Toolkit by Caelaris (info@caelaris.com)', Caelaris_Lib_Cli::CLI_SUCCESS);
        Caelaris_Lib_Cli::nl();
    }
}