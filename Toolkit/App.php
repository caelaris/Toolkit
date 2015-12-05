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
    public $config;

    public function __construct(Container $diContainer)
    {
        $this->diContainer = $diContainer;
    }

    public function init()
    {
        $this->config = $this->diContainer->build('Toolkit\App\Config');
    }

    public function run()
    {
        $this->init();
        $command = $this->config->getCommand();

        $command->execute();
    }
}