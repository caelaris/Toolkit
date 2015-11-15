<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit;

class CommandList
{
    const JSON_FILE_EXTENSION = 'json';
    public $commands;

    public function __construct($configurationFilePath)
    {
        if (!file_exists($configurationFilePath)) {
            throw new \InvalidArgumentException('Command list configuration file does not exist: ' . $configurationFilePath);
        }

        if (pathinfo($configurationFilePath, PATHINFO_EXTENSION) != $this::JSON_FILE_EXTENSION) {
            throw new \InvalidArgumentException('Command list configuration file is not a .json file: ' . $configurationFilePath);
        }

        $content = json_decode(file_get_contents($configurationFilePath), true);
        if (!$content) {
            throw new \InvalidArgumentException('Command list configuration file content is not valid JSON: ' . $configurationFilePath);
        }

        $this->commands = $content;
    }

    public function isRegisteredCommand($command)
    {
        return (bool) array_key_exists($command, $this->commands);
    }
}