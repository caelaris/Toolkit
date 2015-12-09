<?php

/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit\Command;

use Cli\Writer;
use Toolkit\App\Config;
use Toolkit\CommandInterface;

class Help implements CommandInterface
{
    /** @var Config  */
    protected $config;

    /** @var  Writer */
    protected $writer;

    /** @var  Array */
    protected $arguments;

    /**
     * @param Config $config
     * @param Writer $writer
     */
    public function __construct(
        Config $config,
        Writer $writer
    ) {
        $this->config = $config;
        $this->writer = $writer;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        if ($this->config->isModeHelp()) {
            $this->writer->write('toolkit help command: help mode');
        } elseif ($this->config->isModeVerbose()) {
            $this->writer->write('toolkit help command: verbose mode');
        } else {
            $this->writer->write('toolkit help command: normal mode');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
}
