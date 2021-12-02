<?php

declare(strict_types=1);

$file = dirname(__DIR__, 3).'/codeception/bootstrap-global.php';

if ( ! defined('VIEW_TEST_DIR')) {
    define('VIEW_TEST_DIR', __DIR__);
}

require_once $file;