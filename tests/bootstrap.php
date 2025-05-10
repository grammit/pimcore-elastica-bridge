<?php

declare(strict_types=1);

use Pimcore\Bootstrap;

if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '';
}

if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = '';
}

if (!defined('PIMCORE_PROJECT_ROOT')) {
    define('PIMCORE_PROJECT_ROOT', __DIR__ . '/_app');
}

Bootstrap::setProjectRoot();
Bootstrap::bootstrap();
Bootstrap::kernel();
