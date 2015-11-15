<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit;

class App
{
    public $mode;

    public $activeCommand;

    public $arguments;

    public $commandList;

    const MODE_VERBOSE = 'verbose';
    const MODE_HELP = 'help';

    public function __construct(CommandList $commandList)
    {
        $this->commandList = $commandList;
    }

    public function init()
    {
        if (!defined('TOOLKIT_BASE_PATH')) {
            define('TOOLKIT_BASE_PATH', dirname(__FILE__));
        }
        $this->parseOptions();
        $this->parseArguments();
    }

    /**
     * Parse options and set the application mode
     * Currently only --help(-h) and --verbose(-v) are supported
     */
    protected function parseOptions()
    {
        $options = getopt('hv', array('help','verbose'));

        if (isset($options['h']) || isset($options['help'])) {
            $this->mode = self::MODE_HELP;
        } elseif (isset($options['v']) || isset($options['verbose'])) {
            $this->mode = self::MODE_VERBOSE;
        }
    }

    protected function parseArguments()
    {
        $arguments = $_SERVER['argv'];
        foreach ($arguments as $argument) {
            /** All arguments after the command are added to the argument list  */
            if ($this->activeCommand) {
                $this->arguments[] = $argument;
            }

            /** First recognisable argument is set as command */
            if ($this->commandList->isRegisteredCommand($argument)) {
                $this->activeCommand = $argument;
            }
        }
    }
}