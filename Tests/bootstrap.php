<?php

function includeIfExists($file) {
    if (file_exists($file)) {
        return include $file;
    }
}

$vendor = realpath(substr(__DIR__, 0, strpos(__DIR__, 'vendor')) . 'vendor');

if (!$loader = includeIfExists($vendor.'/autoload.php')) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}

$loader->add('Ali', __DIR__);