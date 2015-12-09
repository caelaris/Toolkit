<?php
/**
 * @copyright   2015 Tom Stapersma, Caelaris
 * @license     MIT
 * @author      Tom Stapersma (info@caelaris.com)
 */
namespace Toolkit;

interface CommandInterface
{
    /**
     * Execute the command
     *
     * @return void
     */
    public function execute();

    /**
     * Set the CLI arguments for the command
     *
     * @param $arguments
     *
     * @return CommandInterface
     */
    public function setArguments($arguments);
}