<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit;

use Cli\WriterInterface;
use DI\Container;
use Toolkit\App\Config;

class App
{
    /**
     * @var Container
     */
    protected $diContainer;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @param Container        $diContainer
     * @param Config           $config
     * @param WriterInterface  $writer
     */
    public function __construct(
        Container $diContainer,
        Config $config,
        WriterInterface $writer
    ) {
        $this->diContainer = $diContainer;
        $this->config = $config;
        $this->writer = $writer;
    }

    /**
     * Run the application
     */
    public function run()
    {
        try {
            $command = $this->getConfig()->getCommand();
            $command->execute();
        } catch (\Exception $e) {
            /** If an exception occurs, write it out to CLI */
            $this->writer->write($e->getMessage());
        }
    }

    /**
     * Gets config object
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
