<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit\Tests;

class AppTest extends ToolkitBaseTest
{
    public $className = 'Toolkit\App';

    public function testToolkitAppExists()
    {
        $classExists = class_exists($this->className);
        $this->assertTrue($classExists);
    }

    public function testConstructorMethodExists()
    {
        $methodExists = method_exists($this->className, '__construct');
        $this->assertTrue($methodExists);
    }

    public function testMethodInitExists()
    {
        $methodExists = method_exists($this->className, 'init');
        $this->assertTrue($methodExists);
    }

    public function testMethodParseOptionsExists()
    {
        $methodExists = method_exists($this->className, 'parseOptions');
        $this->assertTrue($methodExists);
    }

    public function testMethodParseArgumentsExists()
    {
        $methodExists = method_exists($this->className, 'parseArguments');
        $this->assertTrue($methodExists);
    }

    public function testPropertyCommandListIsCommandListClass()
    {
        /** @var \Toolkit\App $app */
        $app = $this->build($this->className);
        $app->init();
        $this->assertInstanceOf('\Toolkit\CommandList', $app->commandList);
    }

    public function testInitDefinesToolkitPathConstant()
    {
        /** @var \Toolkit\App $app */
        $app = $this->build($this->className);
        $app->init();
        $this->assertTrue(defined('TOOLKIT_BASE_PATH'));
    }

    public function testConstructorShouldTakeParameterForCommandList()
    {
        $reflector = new \ReflectionClass($this->className);
        $constructor = $reflector->getConstructor();

        $constructorParams = $constructor->getParameters();
        $this->assertNotEmpty($constructorParams);

        /** @var \ReflectionParameter $constructorParam */
        $paramFound = false;
        foreach ($constructorParams as $constructorParam) {
            if ($constructorParam->getName() == 'commandList') {
                $paramFound = true;
                $this->assertFalse($constructorParam->isOptional());
                $this->assertNotNull($constructorParam->getClass());
                $this->assertEquals('Toolkit\CommandList', $constructorParam->getClass()->getName());
            }
        }

        $this->assertTrue($paramFound);
    }

    public function testParseArgumentsShouldSetActiveCommandBasedOnArgv()
    {
        $registeredCommand = 'config:list';
        $_SERVER['argv'][] = $registeredCommand;
        $app = $this->build($this->className);
        $app->init();

        $this->assertEquals($registeredCommand, $app->activeCommand);
    }

    public function testParseArgumentsShouldSetArgumentsForCurrentCommandBasedOnArgv()
    {
        $registeredCommand = 'config:list';
        $_SERVER['argv'][] = $registeredCommand;
        $_SERVER['argv'][] = 'argumentA';
        $_SERVER['argv'][] = 'argumentB';

        $expected = array(
            'argumentA',
            'argumentB'
        );
        $app = $this->build($this->className);
        $app->init();

        $this->assertEquals($expected, $app->arguments);
    }
}