<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

/**
 * Interface Caelaris_Command_Interface
 */
interface Caelaris_Command_Interface
{
     public static function execute();
     public static function help();
}