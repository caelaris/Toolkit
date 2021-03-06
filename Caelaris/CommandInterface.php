<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Caelaris;

/**
 * Interface CommandInterface
 */
interface CommandInterface
{
     public static function execute();
     public static function help();
}