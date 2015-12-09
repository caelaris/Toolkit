<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit;

use DI\Container;

class App
{
    /**
     * @var Container
     */
    public $diContainer;

    /**
     * @var \Toolkit\App\Config
     */
    protected $config;

    public function __construct(Container $diContainer)
    {
        $this->diContainer = $diContainer;
        $this->config = $this->diContainer->build('Toolkit\App\Config');
    }

    public function run()
    {
        $command = $this->config->getCommand();

        $command->execute();
    }

    public function getConfig()
    {
        return $this->config;
    }
}
