<?php

declare(strict_types=1);

if ( ! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$root_dir = getenv('WP_ROOT_FOLDER').DIRECTORY_SEPARATOR.'framework';
$repository_root_dir = getenv('REPOSITORY_ROOT_DIR');

if ( ! defined('CODECEPTION_DIR')) {
    define('CODECEPTION_DIR', $repository_root_dir.DS.'codeception');
}

if ( ! defined('SHARED_FIXTURES_DIR')) {
    define('SHARED_FIXTURES_DIR', CODECEPTION_DIR.DS.'shared-fixtures');
}


//$tests_dir = $root_dir.DIRECTORY_SEPARATOR.'tests';
//

//
//if ( ! defined('ROOT_DIR')) {
//    define('ROOT_DIR', $root_dir);
//}
//
//if ( ! defined('CORE_DIR')) {
//    define('CORE_DIR', $root_dir.'/packages/core');
//}
//
//if ( ! defined('TESTS_DIR')) {
//    define('TESTS_DIR', $root_dir.DS.'tests');
//}
//
//if ( ! defined('TEST_APP_KEY')) {
//    define('TEST_APP_KEY', 'base64:LOK1UydvZ50A9iyTC2KxuP/C6k8TAM4UlGDcjwsKQik=');
//}
//
//if ( ! defined('SITE_URL')) {
//    define('SITE_URL', trim(getenv('FLUSHABLE_SITE_WP_URL'), '/'));
//}
//
//if ( ! defined('FIXTURES_DIR')) {
//    define('FIXTURES_DIR', $tests_dir.DS.'fixtures');
//}
//
//if ( ! defined('ROUTES_DIR')) {
//    define('ROUTES_DIR', FIXTURES_DIR.DS.'routes');
//}
//
//if ( ! defined('VIEWS_DIR')) {
//    define('VIEWS_DIR', $tests_dir.DS.'fixtures'.DS.'views');
//}
//if ( ! defined('BLADE_CACHE')) {
//    define('BLADE_CACHE', TESTS_DIR.DS.'integration'.DS.'blade'.DS.'fixtures'.DS.'cache');
//}
//
//if ( ! defined('BLADE_VIEWS')) {
//    define('BLADE_VIEWS', TESTS_DIR.DS.'integration'.DS.'blade'.DS.'fixtures'.DS.'views');
//}

//require $root_dir.DS.'vendor'.DS.'autoload.php';


