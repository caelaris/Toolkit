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

    /** @var  Caelaris_Config */
    public static $activeConfig;

    const MODE_VERBOSE = 'verbose';
    const MODE_HELP = 'help';

    public static function init()
    {
        define('TOOLBOX_BASE', dirname(__FILE__));
        self::parseOptions();

        self::loadCommands();
        self::parseArguments();
    }

    /**
     * Application entry point
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
     * Parse all parameters into options, command and arguments
     */
    protected static function parseArguments()
    {
        $arguments = $_SERVER['argv'];
        foreach ($arguments as $argument) {
            if (self::$command) {
                self::$arguments[] = $argument;
            }

            if (isset(self::$commandList[$argument])) {
                self::$command = $argument;
            }
        }

    }

    /**
     *
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

    public static function loadCommands()
    {
        if (!defined('TOOLBOX_BASE')) {
            throw new Exception('ERROR: TOOLBOX_BASE not defined');
        }
        $filePath = TOOLBOX_BASE . DIRECTORY_SEPARATOR .'conf' . DIRECTORY_SEPARATOR . 'commands.json';

        if (!file_exists($filePath)) {
            throw new Exception('ERROR: cannot load command list');
        }

        $commands = json_decode(file_get_contents($filePath), true);

        if (empty($commands)) {
            throw new Exception('ERROR: command conf file is not correct json');
        }

        foreach ($commands as $command => $className) {
            if (!class_exists($className)) {
                if (!self::isVerboseMode()) {
                    Caelaris_Lib_Cli::write('ERROR: class does not exist : ' . $className, Caelaris_Lib_Cli::CLI_DEBUG, 1);

                }
                continue;
            }

            $interfaces = class_implements($className);

            if (!in_array('Caelaris_Command_Interface', $interfaces)) {
                if (!self::isVerboseMode()) {
                    Caelaris_Lib_Cli::write('ERROR: ' . $className . ' is not a valid command class', Caelaris_Lib_Cli::CLI_DEBUG, 1);

                }
                continue;
            }

            self::$commandList[$command] = $className;
        }

        if (empty(self::$commandList)) {
            throw new Exception('ERROR: no commands configured');
        }
    }

    public static function getConfig()
    {
        $configurations = Caelaris_Command_Config_List::getConfigurations();
        if (!in_array(self::$arguments[0], $configurations)) {
            throw new Exception('ERROR: No valid configuration argument passed');
        }

        if (!self::$activeConfig) {
            self::$activeConfig = new Caelaris_Config(self::$arguments[0]);
        }

        return self::$activeConfig;
    }

    public static function isHelpMode()
    {
        return (self::$mode == self::MODE_HELP);
    }

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
        Caelaris_Lib_Cli::write('  _____         _   _   _ _   ', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write(' |_   _|__  ___| | | |_(_) |_ ', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write('   | |/ _ \/ _ \ | | / / |  _|', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write('   |_|\___/\___/_| |_\_\_|\__|', Caelaris_Lib_Cli::CLI_INFO);
        Caelaris_Lib_Cli::write('Toolkit by Caelaris (info@caelaris.com)', Caelaris_Lib_Cli::CLI_SUCCESS);

        Caelaris_Lib_Cli::nl();
        Caelaris_Lib_Cli::write('Usage:', Caelaris_Lib_Cli::CLI_DEBUG);
        Caelaris_Lib_Cli::write('command [options] [arguments]', false, 1);
        Caelaris_Lib_Cli::write('Options', Caelaris_Lib_Cli::CLI_DEBUG);
        Caelaris_Lib_Cli::write(' --help (-h)', Caelaris_Lib_Cli::CLI_SUCCESS, 1);
        Caelaris_Lib_Cli::write(' --verbose (-v)', Caelaris_Lib_Cli::CLI_SUCCESS, 1);
        Caelaris_Lib_Cli::write('Available commands:', Caelaris_Lib_Cli::CLI_DEBUG);
        Caelaris_Lib_Cli::write('help', Caelaris_Lib_Cli::CLI_SUCCESS, 1);
        Caelaris_Lib_Cli::write('commands', Caelaris_Lib_Cli::CLI_DEBUG);

        foreach (self::$commandList as $command => $class) {
            Caelaris_Lib_Cli::write($command, Caelaris_Lib_Cli::CLI_SUCCESS, 1);
        }
        exit;
    }
}