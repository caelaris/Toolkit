<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Caelaris;

use Caelaris\Command\Config\All;
use Caelaris\Lib\Cli;

/**
 * Class Toolkit
 *
 * @package Caelaris
 */
class Toolkit
{
    /** Configuration directories and files */
    const CONF_DIR = 'conf';
    const CONF_DIR_EXTENSIONS = 'extensions';
    const CONF_COMMAND_FILENAME = 'commands.json';

    /** @var array A list of all valid commands configured */
    protected static $commandList = array();

    /** @var string Current active command */
    public static $command;

    /** @var  array All options passed to the current command */
    public static $options;

    /** @var  array All arguments passed to the current command */
    public static $arguments;

    /** @var  string Application mode */
    public static $mode;

    /** @var  Config */
    public static $activeConfig;

    const MODE_VERBOSE = 'verbose';
    const MODE_HELP = 'help';

    /**
     * Initialize application, define root dir, parse options, arguments and command
     *
     * @throws \Exception
     */
    public static function init()
    {
        define('TOOLKIT_BASE', dirname(__FILE__));

        self::registerAutoloader();
        self::parseOptions();
        self::loadCommands();
        self::parseArguments();
    }

    /**
     * Registers autoloader for Caelaris
     *
     * @see http://www.php-fig.org/psr/psr-4/examples/
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(function ($class) {
            /** Project-specific namespace prefix */
            $prefix = 'Caelaris\\';

            /** Base directory for the namespace prefix */
            $base_dir = __DIR__ . '/Caelaris/';

            /** Does the class use the namespace prefix? */
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                /** No, move to the next registered autoloader */
                return;
            }

            /** Get the relative class name */
            $relative_class = substr($class, $len);

            /**
             * Replace the namespace prefix with the base directory, replace namespace
             * separators with directory separators in the relative class name, append
             * with .php
             */
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            /** if the file exists, require it */
            if (file_exists($file)) {
                require $file;
            }
        });
    }

    /**
     * Application entry point, initialize application and execute command
     *
     * @throws \Exception
     */
    public static function run()
    {
        try {
            self::init();

            /** If no command is set, echo manPage and stop execution */
            if (!self::$command) {
                self::manPage();
            }

            self::executeCommand();
        } catch (\Exception $e) {
            Cli::write($e->getMessage(), Cli::CLI_ERROR);
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
     * Currently only --help(-h) and --verbose(-v) are supported
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
     * @throws \Exception
     */
    public static function loadCommands()
    {
        if (!defined('TOOLKIT_BASE')) {
            /** TOOLKIT_BASE needs to be defined to locate configuration */
            throw new \Exception('ERROR: TOOLKIT_BASE not defined');
        }

        $filePath = TOOLKIT_BASE . DIRECTORY_SEPARATOR . self::CONF_DIR . DIRECTORY_SEPARATOR . self::CONF_COMMAND_FILENAME;

        if (!file_exists($filePath)) {
            /** If there is no command configuration file, the application cannot run */
            throw new \Exception('ERROR: cannot load command list');
        }

        $commands = json_decode(file_get_contents($filePath), true);

        if (empty($commands)) {
            /** If the command configuration file is empty or not valid JSON, the application cannot run */
            throw new \Exception('ERROR: command conf file is not correct json');
        }

        /** Loop through configured commands and check them for validity */
        foreach ($commands as $command => $className) {
            if (self::isValidCommandClass($className)) {
                self::$commandList[$command] = $className;
            }
        }

        if (empty(self::$commandList)) {
            /** If no valid commands are found, the application cannot run */
            throw new \Exception('ERROR: no commands configured');
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
            Cli::writeVerbose('ERROR: command class does not exist : ' . $className);
            return false;
        }

        $interfaces = class_implements($className);
        if (!in_array('Caelaris\CommandInterface', $interfaces)) {
            /** If the class does not extend Caelaris\CommandInterface it is not a valid command */
            Cli::writeVerbose('ERROR: ' . $className . ' is not a valid command class');
            return false;
        }

        return true;
    }

    /**
     * If the config is not set yet, try to load it
     *
     * @return Config
     * @throws \Exception
     */
    public static function getConfig()
    {
        if (!self::$activeConfig) {
            /** Get all extension configurations */
            $configurations = All::getConfigurations();
            if (!in_array(self::$arguments[0], $configurations)) {
                /** If the first argument is not a valid extension configuration, error out */
                throw new \Exception('ERROR: No valid configuration argument passed');
            }

            /** Load configuration object with data from conf file */
            self::$activeConfig = new Config(self::$arguments[0]);
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
     * @throws \Exception
     */
    public static function executeCommand()
    {
        $commandClassName = self::$commandList[self::$command];

        /** @var \Caelaris\Command $commandClassName */
        $commandClassName::execute();
    }

    /**
     * Write out help info
     */
    public static function manPage()
    {
        self::writeSignature();

        /** Write usage information */
        Cli::write('Usage:', Cli::CLI_DEBUG);
        Cli::write('command [options] [arguments]', false, 1);
        Cli::write('Options', Cli::CLI_DEBUG);
        Cli::write(' --help (-h)', Cli::CLI_SUCCESS, 1);
        Cli::write(' --verbose (-v)', Cli::CLI_SUCCESS, 1);

        /** Write available default commands */
        Cli::write('Default commands:', Cli::CLI_DEBUG);
        Cli::write('help', Cli::CLI_SUCCESS, 1);

        /** Write available custom commands */
        Cli::write('Custom commands:', Cli::CLI_DEBUG);
        foreach (self::$commandList as $command => $class) {
            Cli::write($command, Cli::CLI_SUCCESS, 1);
        }

        /** Exit script execution after man page is written out */
        exit;
    }

    /**
     * Write the signature for the toolkit
     */
    public static function writeSignature()
    {
        Cli::write('  _____         _   _   _ _   ', Cli::CLI_INFO);
        Cli::write(' |_   _|__  ___| | | |_(_) |_ ', Cli::CLI_INFO);
        Cli::write('   | |/ _ \/ _ \ | | / / |  _|', Cli::CLI_INFO);
        Cli::write('   |_|\___/\___/_| |_\_\_|\__|', Cli::CLI_INFO);
        Cli::write('Toolkit by Caelaris (info@caelaris.com)', Cli::CLI_SUCCESS);
        Cli::nl();
    }
}