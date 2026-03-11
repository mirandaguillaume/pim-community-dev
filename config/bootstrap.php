<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

require dirname(__DIR__) . '/vendor/autoload.php';

// Register Symfony's ErrorHandler early so that PHPUnit 10's ErrorHandler
// detects an existing handler and does NOT mask error_reporting().
// Without this, PHPUnit 10 masks E_WARNING/E_NOTICE out of error_reporting(),
// which causes Symfony's ErrorHandler (registered later during kernel boot)
// to silently swallow warnings instead of throwing them as exceptions.
if (class_exists(ErrorHandler::class)) {
    ErrorHandler::register();
}

$dotenv = new Dotenv();
$dotenv->usePutenv(true);
$dotenv->bootEnv(dirname(__DIR__) . '/.env');

$_SERVER += $_ENV;
