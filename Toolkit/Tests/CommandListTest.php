<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit\Tests;

class CommandListTest extends ToolkitBaseTest
{
    public $className = 'Toolkit\CommandList';

    public function testToolkitCommandListExists()
    {
        $classExists = class_exists($this->className);
        $this->assertTrue($classExists);
    }

    public function testConstructorMethodExists()
    {
        $methodExists = method_exists($this->className, '__construct');
        $this->assertTrue($methodExists);
    }

    public function testIsRegisteredCommandMethodExists()
    {
        $methodExists = method_exists($this->className, 'isRegisteredCommand');
        $this->assertTrue($methodExists);
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
            if ($constructorParam->getName() == 'configurationFilePath') {
                $paramFound = true;
                $this->assertFalse($constructorParam->isOptional());
                $this->assertNull($constructorParam->getClass());
            }
        }

        $this->assertTrue($paramFound);
    }

    public function testConstructorParsesJsonFile()
    {
        $expected = array (
            'system:check:translations' => '',
            'config:list' => '',
        );

        /** @var \Toolkit\CommandList $testClass */
        $testClass = $this->container->build($this->className);

        $this->assertEquals($expected, $testClass->commands);
    }

    public function testConstructorShouldThrowExceptionIfFileDoesNotExist()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Command list configuration file does not exist: ');

        $this->container->repository['Toolkit\CommandList']['configurationFilePath'] =
            __DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'non-existent-CommandList.json';

        $this->container->build($this->className);

        $this->container->repository['Toolkit\CommandList']['configurationFilePath'] =
            __DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'CommandList.json';
    }

    public function testConstructorShouldThrowExceptionIfFileIsNotJson()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Command list configuration file is not a .json file: ');

        $this->container->repository['Toolkit\CommandList']['configurationFilePath'] =
            __DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'wrong_extension.txt';

        $this->container->build($this->className);

        $this->container->repository['Toolkit\CommandList']['configurationFilePath'] =
            __DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'CommandList.json';
    }

    public function testConstructorShouldThrowExceptionIfFileContentIsNotJson()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Command list configuration file content is not valid JSON: ');

        $this->container->repository['Toolkit\CommandList']['configurationFilePath'] =
            __DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'not_valid.json';

        $this->container->build($this->className);

        $this->container->repository['Toolkit\CommandList']['configurationFilePath'] =
            __DIR__ . DIRECTORY_SEPARATOR . 'TestsConfiguration' . DIRECTORY_SEPARATOR . 'CommandList.json';
    }

    public function testIsRegisteredMethodShouldReturnTrueForRegisteredCommand()
    {
        $registeredCommand = 'config:list';

        /** @var \Toolkit\CommandList $testClass */
        $testClass = $this->container->build($this->className);

        $this->assertTrue($testClass->isRegisteredCommand($registeredCommand));
    }

    public function testIsRegisteredMethodShouldReturnFalseForNonRegisteredCommand()
    {
        $registeredCommand = 'non:registered:command';

        /** @var \Toolkit\CommandList $testClass */
        $testClass = $this->container->build($this->className);

        $this->assertFalse($testClass->isRegisteredCommand($registeredCommand));
    }
}