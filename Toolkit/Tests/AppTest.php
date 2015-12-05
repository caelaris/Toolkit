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
        $diContainer = new $this->diContainer;

        /** @var \Toolkit\App $app */
        $app = new $this->class($diContainer);

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

    public function testAppDiContainerPropertyShouldBeDIContainerClass()
    {
        $app = $this->getNewApp();
        $this->assertInstanceOf($this->diContainer, $app->diContainer);
    }

    public function testAppClassShouldHaveRunMethod()
    {
        $this->assertTrue(method_exists($this->class, 'run'));
    }

    public function testAppClassShouldHaveInitMethod()
    {
        $this->assertTrue(method_exists($this->class, 'init'));
    }

    public function testInitShouldSetConfigObject()
    {
        $configMock = $this->getMock($this->config);

        $diContainerMock = $this->getMock($this->diContainer);
        $diContainerMock->expects($this->once())->method('build')->with($this->config)->willReturn($configMock);
        /** @var \Toolkit\App $app */
        $app = new $this->class($diContainerMock);

        $app->init();

        $this->assertSame($configMock, $app->config);
    }

    public function testRunShouldCallInitConfigGetCommandCallExecute()
    {
        $commandMock = $this->getMock($this->commandInterface);
        $commandMock->expects($this->once())->method('execute');

        $configMock = $this->getMock($this->config);
        $configMock->expects($this->once())->method('getCommand')->with()->willReturn($commandMock);

        $diContainerMock = $this->getMock($this->diContainer);
        $diContainerMock->expects($this->once())->method('build')->with($this->config)->willReturn($configMock);
        /** @var \Toolkit\App $app */
        $app = new $this->class($diContainerMock);

        $app->run();
        $this->assertSame($configMock, $app->config);
    }
}