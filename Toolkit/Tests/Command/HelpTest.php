<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
class HelpTest extends \PHPUnit_Framework_TestCase
{
    public $class = 'Toolkit\Command\Help';
    public $diContainer = 'DI\Container';
    public $config = 'Toolkit\App\Config';
    public $writer = 'Cli\Writer';


    public function testExecuteShouldEchoContentWithHelpMode()
    {
        $configMock = $this->getMock($this->config, array(), array(), '', false);
        $configMock->expects($this->once())->method('isModeHelp')->willReturn(true);

        $writerMock = $this->getMock($this->writer);
        $writerMock->expects($this->atLeastOnce())->method('write')->withAnyParameters();

        /** @var Toolkit\Command\Help $help */
        $help = new $this->class($configMock, $writerMock);
        $help->execute();
    }
}