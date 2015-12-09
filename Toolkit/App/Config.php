<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit\App;

use DI\Container;

class Config
{
    /** MODE CONSTANTS */
    const MODE_HELP = 'help';
    const MODE_VERBOSE = 'verbose';

    /** Suffix for registering commands with the DI */
    const COMMAND_SUFFIX = 'Command';

    /** @var string Current application mode */
    public $mode;

    /** @var Container DI Container used for building command classes */
    protected $diContainer;

    /** @var string Default command for application */
    protected $defaultCommand;

    /**
     * Constructor
     *
     * @param Container $diContainer
     * @param null      $options
     */
    public function __construct(Container $diContainer, $options = null)
    {
        $this->diContainer = $diContainer;
        $this->parseMode($options);
    }

    /**
     * Parse the application mode from options or getopt()
     *
     * @param null|array $options
     *
     * @return $this
     */
    protected function parseMode($options = null)
    {
        /** If no options are passed to the constructor, read options from the command line */
        if (!$options) {
            $options = getopt('hv', array('help', 'verbose'));
        }

        /** Set mode depending on options, help mode takes precedence over verbose */
        if (isset($options['h']) || isset($options['help'])) {
            $_SERVER['argv'] = array_diff($_SERVER['argv'], array('-h', '--help'));
            $this->mode = $this::MODE_HELP;
        } elseif (isset($options['v']) || isset($options['verbose'])) {
            $_SERVER['argv'] = array_diff($_SERVER['argv'], array('-v', '--verbose'));
            $this->mode = $this::MODE_VERBOSE;
        }

        return $this;
    }

    /**
     * @return \Toolkit\CommandInterface
     */
    public function getCommand()
    {
        /** Get the CLI arguments from $_SERVER */
        $arguments = $_SERVER['argv'];

        /** Remove script name from arguments */
        unset($arguments[0]);

        /** Get command argument */
        $commandArgument = current($arguments);

        /** If no command is passed, get default command */
        if (empty($commandArgument)) {
            $commandArgument = $this->defaultCommand;
            /**
             * @todo throw exception if default command is also not set
             */
        }


        /** Remove command argument from list, following options are command arguments */
        unset($arguments[key($arguments)]);

        $command = $this->diContainer->build($commandArgument . $this::COMMAND_SUFFIX);
        $command->setArguments($arguments);

        return $command;
    }

    /**
     * Returns if the application is currently in help mode
     *
     * @return bool
     */
    public function isModeHelp()
    {
        return ($this->mode === $this::MODE_HELP);
    }

    /**
     * Returns if the application is currently in verbose mode
     *
     * @return bool
     */
    public function isModeVerbose()
    {
        return ($this->mode === $this::MODE_VERBOSE);
    }

    /**
     * Registers a new command with the DI. Optionally registers command as default command.
     *
     * @param      $command
     * @param      $di
     * @param bool $default
     *
     * @return $this
     */
    public function registerCommand($command, $di, $default = false)
    {
        /** Create the command registration name by adding the suffix */
        $registeredCommand = $command . $this::COMMAND_SUFFIX;

        if (is_string($di) && class_exists($di)) {
            /** If DI is a class name, reformat to correct format */
            $registeredDi = array('class' => $di);
        } elseif (is_array($di) && (isset($di['class']) || isset($di['instance']))) {
            /** If DI is an array and has a class or instance key, directly use di */
            $registeredDi = $di;
        } else {
            /** If none of the above, cannot register this command */
            throw new \InvalidArgumentException('Invalid DI passed');
        }

        /** Set default command */
        if ($default) {
            $this->defaultCommand = $command;
        }

        /** Register command with DI Container */
        $this->diContainer->register($registeredCommand, $registeredDi);

        return $this;
    }
}
