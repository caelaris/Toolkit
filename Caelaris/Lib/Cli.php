<?php
/**
 * Adapted and modified from uniconv cli class by Robin de Graaf, devvoh webdevelopment
 * @author Robin de Graaf (hello@devvoh.com)
 * @see https://github.com/devvoh/uniconv
 *
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

namespace Caelaris\Lib;

use Caelaris\Toolkit;

/**
 * Class Cli
 */
class Cli {
    static $parameters = array();
    static $lastProgressLength = 0;
    static $lines = array();

    const CLI_ERROR     = 'error';
    const CLI_SUCCESS   = 'success';
    const CLI_DEBUG     = 'debug';
    const CLI_INFO      = 'info';

    /**
     * @param      $message
     * @param bool $type
     * @param int  $indent
     */
    public static function write($message, $type = false, $indent = 0)
    {
        $string = '';

        if ($type == self::CLI_SUCCESS) {
            $string .= "\033[32m";
        } elseif ($type == self::CLI_ERROR) {
            $string .= "\033[31m";
        } elseif ($type == self::CLI_DEBUG) {
            $string .= "\033[33m";
        } elseif ($type == self::CLI_INFO) {
            $string .= "\033[36m";
        }

        $string .= $message;

        if (false !== $type) {
            $string .= "\033[0m";
        }

        $string = str_pad($string, strlen($string) + ($indent * 4), " ", STR_PAD_LEFT);

        echo $string . PHP_EOL;
    }

    /**
     * @param        $message
     * @param string $type
     * @param int    $indent
     */
    public static function writeVerbose($message, $type = self::CLI_DEBUG, $indent = 1)
    {
        if (Toolkit::isVerboseMode()) {
            self::write($message, $type, $indent);
        }
    }

    /**
     * @param $message
     */
    public static function dump($message)
    {
        print_r($message);
        echo PHP_EOL;
    }

    /**
     * @param $message
     */
    public static function addLine($message)
    {
        self::$lines[] = $message;
    }

    /**
     *
     */
    public static function writeLines()
    {
        $output = implode(self::$lines, PHP_EOL);
        self::write($output);
    }

    /**
     *
     */
    public static function nl()
    {
        echo PHP_EOL;
    }

    /**
     * @param $params
     */
    public static function parseParameters($params)
    {
        // Check for parameters given
        for ($i = 1; $i < count($params); $i++) {
            if (substr($params[$i], 0, 1) === '-') {
                // set the current param as key and the next one as value
                $key = str_replace('-', '', $params[$i]);
                self::$parameters[$key] = $params[$i+1];
                // and skip the value
                $i++;
            } else {
                // Set the parameters as key and true as value
                self::$parameters[$params[$i]] = true;
            }
        }
    }

    /**
     * @return array
     */
    public static function getParameters()
    {
        return self::$parameters;
    }

    /**
     * @param $key
     *
     * @return null
     */
    public static function getParameter($key)
    {
        if (isset(self::$parameters[$key])) {
            return self::$parameters[$key];
        }
        return null;
    }

    /**
     * @param      $question
     * @param bool $default
     *
     * @return bool
     */
    public static function yesNo($question, $default = true)
    {
        // output question and appropriate default value
        echo trim($question) . ($default ? ' [Y/n] ' : ' [y/N] ');
        // get user input from stdin
        $line = fgets(STDIN);
        // turn into lowercase and check specifically for yes and no, call ourselves again if neither
        $value = strtolower(trim($line));
        if (in_array($value, array('y', 'yes'))) {
            return true;
        } elseif (in_array($value, array('n', 'no'))) {
            return false;
        } elseif (empty($value)) {
            // but if it's empty, assume default
            return $default;
        }
        // If nothing has been returned so far, keep asking
        echo "Enter y/yes or n/no.\n";
        return self::yesNo($question, $default);
    }

    /**
     * @param $question
     *
     * @return string
     */
    public static function whichDir($question)
    {
        // output question
        self::write(trim($question), self::CLI_DEBUG);
        // get user input from stdin
        $line = fgets(STDIN);
        // turn into lowercase and check if dir exists, call ourselves again if neither
        $value = trim($line);
        if (is_dir($value)) {
            return $value;
        }

        // If nothing has been returned so far, keep asking
        self::write($value . ' is not a valid directory', self::CLI_ERROR, 1);
        return self::whichDir($question);
    }

    /**
     * @param        $question
     * @param        $callback
     * @param string $errorMessage
     *
     * @return string
     */
    public static function validatedQuestion($question, $callback, $errorMessage = '')
    {
        if (empty($callback) || !(is_string($callback) || is_array($callback))) {
            throw new \InvalidArgumentException('ERROR: No callback function is defined');
        } elseif (is_string($callback) && !function_exists($callback)) {
            throw new \InvalidArgumentException('ERROR: Function ' . $callback . ' does not exist');
        } elseif(is_array($callback) && !method_exists($callback[0], $callback[1])) {
            throw new \InvalidArgumentException('ERROR: Method ' . $callback[1] . ' for class ' . $callback[0] . 'does not exist');
        } elseif (!is_callable($callback)) {
            if (is_array($callback)) {
                $callback = var_export($callback, true);
            }
            throw new \InvalidArgumentException('ERROR: method/function ' . $callback . 'is not callable');
        }

        // output question
        self::write(trim($question), self::CLI_DEBUG);
        // get user input from stdin
        $line = fgets(STDIN);
        // turn into lowercase and check if dir exists, call ourselves again if neither
        $value = trim($line);

        if (call_user_func($callback, $value)) {
            return $value;
        }

        // If nothing has been returned so far, keep asking
        self::write(sprintf($errorMessage, $value), self::CLI_ERROR, 1);
        return self::validatedQuestion($question, $callback, $errorMessage );
    }

    /**
     * @param $message
     */
    public static function progress($message)
    {
        if (self::$lastProgressLength > 0) {
            echo "\e[" . self::$lastProgressLength . "D";
        }
        self::$lastProgressLength = strlen($message);
        echo $message;
    }

    /**
     *
     */
    public static function end()
    {
        self::writeLines();
        exit;
    }
}