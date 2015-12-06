<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public $class = 'Toolkit\App\Config';
    public $diContainer = 'DI\Container';
    public $helpCommand = 'Toolkit\Command\Help';

    public function testConfigClassShouldHaveGetCommandMethod()
    {
        $this->assertTrue(method_exists($this->class, 'getCommand'));
    }

    public function testConfigClassShouldHaveConstructor()
    {
        $this->assertTrue(method_exists($this->class, '__construct'));
    }

    public function testConfigClassShouldHaveIsModeHelp()
    {
        $this->assertTrue(method_exists($this->class, 'isModeHelp'));
    }

    public function testConfigClassShouldHaveIsModeVerbose()
    {
        $this->assertTrue(method_exists($this->class, 'isModeVerbose'));
    }

    /**
     * @dataProvider modeDataProvider
     *
     * @param $options
     * @param $modeMethod
     */
    public function testConfigConstructShouldSetCorrectMode($options, $modeMethod)
    {
        $diContainer = $this->getMock($this->diContainer);

        /** @var Toolkit\App\Config $config */
        $config = new $this->class($diContainer, $options);
        $this->assertTrue($config->$modeMethod());
    }

    public function modeDataProvider()
    {
        return array(
            array(
                array('h' => false),
                'isModeHelp',
            ),
            array(
                array('help' => false),
                'isModeHelp',
            ),
            array(
                array('v' => false),
                'isModeVerbose',
            ),
            array(
                array('verbose' => false),
                'isModeVerbose',
            ),
        );
    }

    public function testGetCommandMethodShouldReturnCommandClassInstance()
    {
        $commandMock = $this->getMock($this->helpCommand, array(), array(), '', false);
        $diContainer = $this->getMock($this->diContainer);
        $_SERVER['argv'] = array('./test.php', 'help');
        $diContainer->expects($this->once())->method('build')->with('helpCommand')->willReturn($commandMock);

        /** @var Toolkit\App\Config $config */
        $config = new $this->class($diContainer);

        $command = $config->getCommand();

        $this->assertSame($commandMock, $command);
    }
}