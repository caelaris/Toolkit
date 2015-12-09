<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit\Tests;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public $class = 'Toolkit\App';
    public $diContainer = 'DI\Container';
    public $config = 'Toolkit\App\Config';
    public $commandInterface = 'Toolkit\CommandInterface';

    public function getNewApp()
    {
        /** @var \DI\Container $diContainer */
        $diContainer = $this->getMock($this->diContainer);
        $config = $this->getMock($this->config, array(), array(), '', false);

        /** @var \Toolkit\App $app */
        $app = new $this->class($diContainer, $config);

        return $app;
    }

    public function testAppClassExists()
    {
        $this->assertTrue(class_exists($this->class));
    }

    public function testAppClassConstructorShouldAcceptDIContainer()
    {
        $appReflection = new \ReflectionClass($this->class);
        $expectedParamClass = $this->diContainer;

        $appConstructor = $appReflection->getConstructor();

        $this->assertNotEmpty($appConstructor);
        $appConstructorParams = $appConstructor->getParameters();

        $this->assertNotEmpty($appConstructorParams);

        $found = false;
        foreach ($appConstructorParams as $param) {
            $paramClass = $param->getClass();
            if ($paramClass && $paramClass->getName() == $expectedParamClass) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    public function testAppClassShouldHaveRunMethod()
    {
        $this->assertTrue(method_exists($this->class, 'run'));
    }

    public function testRunShouldCallGetCommandCallExecute()
    {
        $commandMock = $this->getMock($this->commandInterface);
        $commandMock->expects($this->once())->method('execute');

        $configMock = $this->getMock($this->config, array(), array(), '', false);
        $configMock->expects($this->once())->method('getCommand')->with()->willReturn($commandMock);

        $diContainerMock = $this->getMock($this->diContainer);
        /** @var \Toolkit\App $app */
        $app = new $this->class($diContainerMock, $configMock);

        $app->run();
        $this->assertSame($configMock, $app->getConfig());
    }

    public function testShouldHaveGetConfigMethod()
    {
        $this->assertTrue(method_exists($this->class, 'getConfig'));
    }

    /**
     * @depends testShouldHaveGetConfigMethod
     */
    public function testGetConfigShouldReturnSameConfigObject()
    {
        $configMock = $this->getMock($this->config, array(), array(), '', false);
        $diContainerMock = $this->getMock($this->diContainer);

        /** @var \Toolkit\App $app */
        $app = new $this->class($diContainerMock, $configMock);

        $this->assertSame($configMock, $app->getConfig());
    }
}
