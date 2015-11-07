<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

/**
 * Class Caelaris_Command_System_Check_Translation
 */
class Caelaris_Command_System_Check_Translation extends Caelaris_Command_Config
{
    public static $name = 'system:check:translations';

    /**
     * @return void
     * @throws Exception
     */
    protected static function executeCommand()
    {
        Caelaris_Tools_Magento_Extension::checkSystemXmlTranslations();
        return;
    }
}