<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Caelaris\Tools\Magento;

use Caelaris\Config;
use Caelaris\Lib\Cli;
use Caelaris\Toolkit;
use Caelaris\Tools\Magento;

/**
 * Class Extension
 * Tools specific for Magento extensions
 *
 * @package Caelaris\Tools\Magento
 */
class Extension extends Magento
{
    /**
     * Resets sort order for a Magento extension's system.xml file
     * @todo implement a 'Check'-method
     * @todo improve feedback
     *
     * @param Config $config
     * @param bool                         $return
     * @param array                        $skip
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public static function fixSystemXmlSort(Config $config, $return = true, $skip = array())
    {
        $increment = $config->getSystemXmlSortIncrement();
        $simpleXml = simplexml_load_file($config->getSystemXmlPath());

        if ($simpleXml === false) {
            throw new \Exception('ERROR: Failed to load file: ' . $config->getSystemXmlPath());
        }

        foreach ($simpleXml->sections->children() as $section) {
            if (!$section->groups) {
                continue;
            }
            $sectionName = $section->getName();

            if (in_array($sectionName, $skip)) {
                continue;
            }

            $exceptions = $config->getSystemXmlSortExceptions();

            foreach ($section->groups->children() as $group) {
                if (!$group->fields) {
                    continue;
                }

                $groupName = $group->getName();
                if (in_array($sectionName . '/' . $groupName, $skip)) {
                    continue;
                }

                $i = $increment;
                foreach ($group->fields->children() as $field) {
                    $fieldName = $field->getName();
                    if (in_array($sectionName . '/' . $groupName . '/' . $fieldName, $skip)) {
                        continue;
                    }
                    $sortOrder = $i;
                    if (isset($exceptions[$sectionName][$groupName][$fieldName])) {
                        $sortOrder = $exceptions[$sectionName][$groupName][$fieldName];
                    }
                    $field->sort_order = $sortOrder;
                    $i += $increment;
                }
            }
        }

        if ($return) {
            return $simpleXml->asXML();
        }

        return $simpleXml->asXML($config->getSystemXmlPath());
    }

    /**
     * @param Config $config
     * @todo improve feedback
     *
     * @return array
     * @throws \Exception
     */
    public static function checkSystemXmlTranslations(Config $config = null)
    {
        if (is_null($config)) {
            $config = Toolkit::getConfig();
        }

        Cli::writeVerbose('===== Checking system.xml translations =====');
        Cli::writeVerbose('-Loading system.xml');

        $simpleXml = simplexml_load_file($config->getSystemXmlPath());

        if ($simpleXml === false) {
            throw new \Exception('ERROR: Failed to load file: ' . $config->getSystemXmlPath());
        }

        $keys = array();

        Cli::writeVerbose('-Start parsing system.xml');

        foreach ($simpleXml->sections->children() as $section) {
            if (!$section->groups) {
                continue;
            }

            foreach ($section->groups->children() as $group) {
                if (!$group->fields) {
                    continue;
                }
                /** @var \SimpleXmlElement $field */
                foreach ($group->fields->children() as $field) {
                    /** @var \SimpleXmlElement $translateAttribute */
                    $translateAttribute = $field['translate'];
                    if ($translateAttribute) {
                        $translateString = $translateAttribute->__toString();
                        $translateFields = explode(' ', str_replace(',',' ', $translateString));
                        foreach ($translateFields as $translateField) {
                            $key = $field->$translateField->__toString();
                            if (!$key) {
                                continue;
                            }

                            $keys[] = $key;
                        }
                    }
                }
            }
        }

        Cli::writeVerbose('-Finished parsing System.xml');

        if (empty($keys)) {
            throw new \Exception('ERROR: No Translation keys found in the system.xml of extension: ' . $config->getExtensionName(true));
        }

        $keys = array_unique($keys);


        $missing = array();
        foreach ($config->getLocales() as $locale) {
            Cli::writeVerbose('-Start checking locale: ' . $locale);
            $found = array();
            $f = fopen($config->getLocaleDir() . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $config->getExtensionName(true) . ".csv", "r");
            while ($row = fgetcsv($f)) {
                foreach ($keys as $key) {
                    if ($row[0] == $key || $row[0] == $config->getExtensionName(true) . '::' . $key) {
                        $found[] = $key;
                        break;
                    }
                }
            }
            fclose($f);
            $missing[$locale] = array_diff($keys, $found);
                Cli::writeVerbose('-Finished checking locale: ' . $locale);
        }

        $success = empty($missing);
        foreach ($missing as $locale => $keys) {
            Cli::write('--------- Missing keys for locale: ' . $locale . ' ---------', Cli::CLI_DEBUG, 1);
            foreach ($keys as $key) {
                Cli::write($key, Cli::CLI_ERROR, 2);
            }
        }

        if ($success) {
            Cli::write($config->getExtensionName(true) . ' has no missing translations for system.xml', Cli::CLI_SUCCESS, 1);
        }

        Cli::writeVerbose('===== Finished checking system.xml translations =====');

        return $success;
    }

    /**
     * @param Config $config
     * @todo improve feedback
     *
     * @return bool
     * @throws \Exception
     */
    public static function checkAdminIsAllowedMethod(Config $config)
    {
        Cli::write('===== Checking ' . $config->getExtensionName(true) . ' for SUPEE-6285 ====', Cli::CLI_DEBUG);
        $controllers = $config->getControllers();
        if (empty($controllers)) {
            Cli::write($config->getExtensionName(true) . ' has no controllers', Cli::CLI_SUCCESS, 1);
            return true;
        }

        $success = true;
        foreach ($controllers as $path => $controllerClass) {
            require $path;

            $reflector = new \ReflectionClass($controllerClass);
            if (!$reflector->isSubclassOf('Mage_Adminhtml_Controller_Action')) {
                continue;
            }

            if (!$reflector->hasMethod('_isAllowed')) {
                $success = false;
                Cli::write($controllerClass . ' does not have a custom _isAllowed method', Cli::CLI_ERROR, 1);
                continue;
            }

            if ($reflector->getMethod('_isAllowed')->class == 'Mage_Adminhtml_Controller_Action') {
                $success = false;
                Cli::write($controllerClass . ' does not have a custom _isAllowed method', Cli::CLI_ERROR, 1);
                continue;
            }
        }

        if ($success) {
            Cli::write($config->getExtensionName(true) . ' is patched for Magento patch SUPEE-6285', Cli::CLI_SUCCESS, 1);
        }

        Cli::write('===== Finished checking ' . $config->getExtensionName(true) . ' for SUPEE-6285 ====', Cli::CLI_DEBUG);
        return $success;
    }
}