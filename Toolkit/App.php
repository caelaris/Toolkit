<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit;

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
     * @param Container $diContainer
     * @param Config    $config
     */
    public function __construct(Container $diContainer, Config $config)
    {
        $this->diContainer = $diContainer;
        $this->config = $config;
    }

    /**
     * Run the application
     */
    public function run()
    {
        $command = $this->config->getCommand();

        $command->execute();
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
