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
    const MODE_HELP = 'help';
    const MODE_VERBOSE = 'verbose';

    const COMMAND_POSTFIX = 'Command';

    public $mode;
    protected $diContainer;

    public function __construct(Container $diContainer, $options = null)
    {
        $this->diContainer = $diContainer;
        $this->parseMode($options);
    }

    protected function parseMode($options = null)
    {
        /** If no options are passed to the constructor, read options from the command line */
        if (!$options) {
            $options = getopt('hv', array('help', 'verbose'));
        }

        /** Set mode depending on options */
        if (isset($options['h']) || isset($options['help'])) {
            $_SERVER['argv'] = array_diff($_SERVER['argv'], array('-h', '--help'));
            $this->mode = $this::MODE_HELP;
        } elseif (isset($options['v']) || isset($options['verbose'])) {
            $_SERVER['argv'] = array_diff($_SERVER['argv'], array('-v', '--verbose'));
            $this->mode = $this::MODE_VERBOSE;
        }
    }

    /**
     * @return \Toolkit\CommandInterface
     */
    public function getCommand()
    {
        $arguments = $_SERVER['argv'];

        /** remove script name from arguments */
        unset($arguments[0]);

        /** Get command argument */
        $commandArgument = current($arguments);

        /** Remove command argument from list, following options are constructor params */
        unset($arguments[key($arguments)]);

        /**
         * @todo add support for constructor params
         */
        return $this->diContainer->build($commandArgument . $this::COMMAND_POSTFIX);
    }

    public function isModeHelp()
    {
        return ($this->mode === $this::MODE_HELP);
    }

    public function isModeVerbose()
    {
        return ($this->mode === $this::MODE_VERBOSE);
    }
}