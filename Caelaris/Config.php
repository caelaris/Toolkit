<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Caelaris;

use Caelaris\Autoloader\Magento;
use Caelaris\Lib\Cli;

/**
 * Class Config
 */
class Config
{
    protected $_configName;
    protected $_namespace;
    protected $_extensionName;
    protected $_codePool;
    protected $_baseDir;
    protected $_appDir = 'app';
    protected $_codeDir = 'code';
    protected $_localeDir = 'locale';
    protected $_etcDir = 'etc';
    protected $_controllersDir = 'controllers';
    protected $_systemXmlFilename = 'system.xml';
    protected $_locales = array('nl_NL', 'en_US');
    protected $_controllers;

    protected $_systemXmlSortExceptions = array();
    protected $_systemXmlSortIncrement = 10;

    /**
     * @param null $name
     *
     * @throws \Exception
     */
    public function __construct($name = null)
    {
        if (is_null($name)) {
            throw new \Exception('ERROR: No configuration name given');
        }

        $this->setConfigName($name)
            ->initialize()
            ->checkConfigRequirements()
            ->setMagentoAutoloader();
    }

    /**
     * Initialize configuration from conf file
     *
     * @throws \Exception
     *
     * @return $this
     */
    protected function initialize()
    {
        if (!defined('TOOLKIT_BASE')) {
            throw new \Exception('ERROR: TOOLKIT_BASE not defined');
        }

        $name = $this->_configName;

        /** Build the file path to the configuration file */
        $path = array(
            TOOLKIT_BASE,
            Toolkit::CONF_DIR,
            Toolkit::CONF_DIR_EXTENSIONS,
            $name . '.json'
        );

        $filePath = implode(DIRECTORY_SEPARATOR, $path);
        if (!file_exists($filePath)) {
            /** If the configuration file does not exist, error out */
            throw new \Exception('ERROR: Configuration file for ' . $name . ' does not exist');
        }

        $configJson = json_decode(file_get_contents($filePath), true);

        /** Set all configuration information if applicable */
        if (isset($configJson['namespace'])) {
            $this->setNamespace($configJson['namespace']);
        }

        if (isset($configJson['extension'])) {
            $this->setExtensionName($configJson['extension']);
        }

        if (isset($configJson['codePool'])) {
            $this->setCodePool($configJson['codePool']);
        }

        if (isset($configJson['system']['sort']['exceptions'])) {
            $this->setSystemXmlSortExceptions($configJson['system']['sort']['exceptions']);
        }

        /** If a base directory has been configured, use that, otherwise ask the user to provide it */
        if (isset($configJson['baseDir']) && is_dir($configJson['baseDir'])) {
            $baseDir = $configJson['baseDir'];
        } else {
            $baseDir = Cli::whichDir('What is the base directory for ' . $this->getExtensionName() . '?');
        }

        $this->setBaseDir($baseDir);

        return $this;
    }

    /**
     * Set the name of the current configuration
     *
     * @param $name
     *
     * @return $this
     */
    public function setConfigName($name)
    {
        $this->_configName = $name;
        return $this;
    }

    /**
     * Registers an autoloader to read Magento files
     *
     * @return $this
     */
    protected function setMagentoAutoloader()
    {
        $base = $this->_baseDir;
        /**
         * Set include path
         */
        $paths = array();
        $paths[] = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'local';
        $paths[] = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'community';
        $paths[] = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'core';
        $paths[] = $base . DIRECTORY_SEPARATOR . 'lib';

        $appPath = implode(PATH_SEPARATOR, $paths);
        set_include_path($appPath . PATH_SEPARATOR . get_include_path());

        Magento::register();

        return $this;
    }

    /**
     * Check if the configuration has loaded all requirements
     * @todo add user questions if a requirement is missing
     *
     * @return $this
     * @throws \Exception
     */
    protected function checkConfigRequirements()
    {
        $errors = array();
        if (!$this->_namespace) {
            $errors[] = 'No namespace is defined';
        }

        if (!$this->_extensionName) {
            $errors[] = 'No extension name is defined';
        }

        if (!$this->_codePool) {
            $errors[] = 'No code pool is defined';
        }

        if (!$this->_baseDir) {
            $errors[] = 'No base directory is defined';
        }

        if (!is_file($this->getSystemXmlPath())) {
            $errors[] = 'system.xml located at ' . $this->getSystemXmlPath() .  ' is not a file';
        }

        if (!empty($errors)) {
            /**
             * @todo improve multiple error handling (using CLI write and then throwing exception)
             */
            $exceptionString = 'ERROR: Requirements failed: ';
            $exceptionString .= implode(' & ', $errors);
            throw new \Exception($exceptionString);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSystemXmlSortIncrement()
    {
        return $this->_systemXmlSortIncrement;
    }

    /**
     * @return array
     */
    public function getSystemXmlSortExceptions()
    {
        return $this->_systemXmlSortExceptions;
    }

    /**
     * @param $systemXmlSortExceptions
     *
     * @return array
     */
    public function setSystemXmlSortExceptions($systemXmlSortExceptions)
    {
        $this->_systemXmlSortExceptions = $systemXmlSortExceptions;
        return $this;
    }

    /**
     * Return the extension's name with an optional parameter to include the namespace too
     *
     * @param bool $includesNamespace
     *
     * @return string
     */
    public function getExtensionName($includesNamespace = false)
    {
        $extensionName = '';
        if ($includesNamespace) {
            $extensionName .= $this->getNamespace() . '_';
        }

        $extensionName .= $this->_extensionName;
        return $extensionName;
    }

    /**
     * @param $extensionName
     *
     * @return $this
     */
    public function setExtensionName($extensionName)
    {
        $this->_extensionName = $extensionName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * @param $namespace
     *
     * @return mixed
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodePool()
    {
        return $this->_codePool;
    }

    /**
     * @param $codePool
     *
     * @return mixed
     */
    public function setCodePool($codePool)
    {
        $this->_codePool = $codePool;
        return $this;
    }

    /**
     * @param $baseDir
     *
     * @return $this
     */
    public function setBaseDir($baseDir)
    {
        if (substr($baseDir, -1, 1) == DIRECTORY_SEPARATOR) {
            $baseDir = substr($baseDir, 0, strlen($baseDir)-1);
        }

        $this->_baseDir = $baseDir;

        return $this;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->_locales;
    }

    /**
     * @param $locales
     *
     * @return $this
     */
    public function setLocales($locales)
    {
        $this->_locales = $locales;

        return $this;
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public function getExtensionPath($dir = '')
    {
        $path = array();
        $path[] = $this->_baseDir;
        $path[] = $this->_appDir;
        $path[] = $this->_codeDir;
        $path[] = $this->_codePool;
        $path[] = $this->_namespace;
        $path[] = $this->_extensionName;
        if (!empty($dir)) {
            $path[] = $dir;
        }

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @return string
     */
    public function getLocaleDir()
    {
        $path = array();
        $path[] = $this->_baseDir;
        $path[] = $this->_appDir;
        $path[] = $this->_localeDir;

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @return string
     */
    public function getControllersPath()
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->getExtensionPath());
        $path[] = $this->_controllersDir;

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @return string
     */
    public function getSystemXmlPath()
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->getExtensionPath());
        $path[] = $this->_etcDir;
        $path[] = $this->_systemXmlFilename;

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Returns all controllers + classnames from
     *
     * @return mixed
     */
    public function getControllers()
    {
        /** If no controllers are set, get controllers */
        if (empty($this->_controllers)) {
            /**
             * @todo add controllers checked class for extensions without controllers
             */
            $controllerDir = $this->getExtensionPath('controllers');
            $this->_controllers = array();

            /** @var \SplFileInfo $file */
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerDir)) as $file)
            {
                /** Filter out directories */
                if ($file->isDir()) {
                    continue;
                }
                /**
                 * @todo add verbose mode support
                 */

                /** Only allow json files */
                if ($file->getExtension() != 'php') {
                    continue;
                }

                $filename = $file->getBaseName();
                $className = $this->parseControllerClass($filename);

                $this->_controllers[$filename] =  $className;
            }
        }

        return $this->_controllers;
    }

    /**
     * Parse a controller class name from a controller file path.
     *
     * @param $filename
     *
     * @return mixed|string
     */
    protected function parseControllerClass($filename)
    {
        $controllerDir = $this->getExtensionPath('controllers');
        /** Remove base directory from path */
        $file = str_replace($controllerDir, '', $filename);

        /** Trim starting slash */
        $file = ltrim($file, DIRECTORY_SEPARATOR);

        /** Remove .php file extension */
        $file = str_replace('.php', '', $file);

        /** Get the extension prefix */
        $classPrefix = $this->getExtensionName(true);

        /** Build class name by adding file name to extension prefix */
        $className = $classPrefix . '_' . $file;

        /** Replace directory separators with underscores */
        $className = str_replace(DIRECTORY_SEPARATOR, '_', $className);

        return $className;
    }
}