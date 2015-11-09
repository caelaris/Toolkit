<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Caelaris\Command\System\Check;

use Caelaris\Command\Config;
use Caelaris\Tools\Magento\Extension;

/**
 * Class Translation
 *
 * @package Caelaris\Command\System\Check
 */
class Translation extends Config
{
    public static $name = 'system:check:translations';

    /**
     * @return void
     * @throws \Exception
     */
    protected static function executeCommand()
    {
        Extension::checkSystemXmlTranslations();
        return;
    }
}