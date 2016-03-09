#!/usr/bin/env php
<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */

#ini_set('display_errors', 1);
require_once('vendor/autoload.php');

/** Run application */
$diContainer = new \DI\Container;
$diContainer->register('Cli\WriterInterface', array('class' => '\Cli\Writer'));

/** @var \Toolkit\App $app */
$app = $diContainer->build('\Toolkit\App');
$app->getConfig()->registerCommand('help', array('class' => '\Toolkit\Command\Help'), true);
$app->run();
