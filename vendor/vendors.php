#!/usr/bin/env php
<?php

/**
 * This file is part of the AliDatatableBundle package from the FOSUserBundle package.
 * 
 * @see FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */

set_time_limit(0);

if (isset($argv[1])) {
    $_SERVER['SYMFONY_VERSION'] = $argv[1];
}

$vendorDir = __DIR__;
$deps = array(
    array('symfony', 'git://github.com/symfony/symfony', isset($_SERVER['SYMFONY_VERSION']) ? $_SERVER['SYMFONY_VERSION'] : 'origin/master'),
    array('twig', 'git://github.com/fabpot/Twig.git', 'origin/master'),
    array('doctrine-common', 'git://github.com/doctrine/common.git', 'origin/master'),
    array('doctrine-dbal', 'git://github.com/doctrine/dbal.git', 'origin/master'),
    array('doctrine', 'git://github.com/doctrine/doctrine2.git', 'origin/master'),
    array('phing', 'git://github.com/Xosofox/phing.git', 'origin/master'),
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        $return = null;
        system(sprintf('git clone -q %s %s', escapeshellarg($url), escapeshellarg($installDir)), $return);
        if ($return > 0) {
            exit($return);
        }
    }

    $return = null;
    system(sprintf('cd %s && git fetch -q origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)), $return);
    if ($return > 0) {
        exit($return);
    }
}
