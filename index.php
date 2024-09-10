<?php

$loader = require __DIR__ . '/vendor/autoload.php';

$config_location = __DIR__ . '/config.json';

if (count($argv) > 1) {
    $config_location = $argv[1];
}

$config = new \Config\Config($config_location);
$fs_parser = new \FSNavigation\NavigationParser($config->root());
$menu = $fs_parser->parse();
$socket = new NavigableSocket\NavigableSocket(
    $config->host(),
    $config->port(),
    5,
    $menu
);
$socket->loop();
