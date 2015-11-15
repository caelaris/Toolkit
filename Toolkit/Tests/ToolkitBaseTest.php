<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit\Tests;

use DI\Container;
use DI\JsonLoader;

class ToolkitBaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var Container */
    public $container;

    protected function setUp()
    {
        $jsonLoader = new JsonLoader(__DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'PHPUnit.json');
        $this->container = new Container($jsonLoader);

        $this->container->repository['Toolkit\CommandList']['configurationFilePath'] =
            __DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'CommandList.json';
    }

    protected function build($className)
    {
        return $this->container->build($className);
    }

    /**
     * This method is only here to prevent PHPUnit from throwing exceptions because this class has no tests
     */
    public function testNothing()
    {

    }
}