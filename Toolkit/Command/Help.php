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

    public function execute()
    {
        if ($this->config->isModeHelp()) {
            $this->writer->write('toolkit help command test mode');
        }
    }
}
