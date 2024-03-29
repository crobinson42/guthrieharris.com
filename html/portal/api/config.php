<?php
date_default_timezone_set('America/New_York');

define('API_VERSION', 1);

/**
 * DIRECTUS_ENV - Possible values:
 *
 *   'production' => error suppression, nonce protection
 *   'development' => no error suppression, no nonce protection (allows manual viewing of API output)
 *   'staging' => no error suppression, no nonce protection (allows manual viewing of API output)
 *   'development_enforce_nonce' => no error suppression, nonce protection
 */
define('DIRECTUS_ENV', 'development');

// MySQL Settings
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'laura201_portal');
define('DB_USER', 'laura201_portal');
define('DB_PASSWORD', 'overlord1');
define('DB_PREFIX', '');
define('DB_ENGINE', 'InnoDB');
define('DB_CHARSET', 'utf8');

define('DB_HOST_SLAVE', ''); //Leave undefined to fall back on master
define('DB_USER_SLAVE', '');
define('DB_PASSWORD_SLAVE', '');

// Url path to Directus
define('DIRECTUS_PATH', '/portal/');


$host = 'www.example.com'; // (Make it work for CLI)
if (isset($_SERVER['SERVER_NAME'])) {
    $host = $_SERVER['SERVER_NAME'];
}

define('ROOT_URL', '//' . $host);
if (!defined('ROOT_URL_WITH_SCHEME')) {
    //Use this for emailing URLs(links, images etc) as some clients will trip on the scheme agnostic ROOT_URL
    define('ROOT_URL_WITH_SCHEME', 'https://' . $host);
}

// Absolute path to application
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));

//Memcached Server, operates on default 11211 port.
define('MEMCACHED_SERVER', '127.0.0.1');

//Namespaced the memcached keys so branches/databases to not collide
//options are prod, staging, testing, development
define('MEMCACHED_ENV_NAMESPACE', 'staging');

define('STATUS_DELETED_NUM', 0);
define('STATUS_ACTIVE_NUM', 1);
define('STATUS_DRAFT_NUM', 2);
define('STATUS_COLUMN_NAME', 'active');
