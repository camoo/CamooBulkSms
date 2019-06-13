<?php
/**
 * Copyright 2019 Camoo Sarl.
 *
 */
define('CAMOO_SMS_SRC_DIR', __DIR__.'/src/');

if (version_compare(PHP_VERSION, '7.1.0', '<')) {
    throw new Exception('The CAMOO SMS Library requires PHP version 7.1.0 or higher.');
}

/**
 * Register the autoloader for the CAMOO SMS classes.
 * Based off the official PSR-4 autoloader example found here:
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'Camoo\\Sms\\';

    // base directory for the namespace prefix
    $base_dir = defined('CAMOO_SMS_SRC_DIR') ? CAMOO_SMS_SRC_DIR : __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = mb_strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = mb_substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});
