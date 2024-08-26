<?php

$loader = require __DIR__ . '/vendor/autoload.php';

$fs_parser = new \FSNavigation\NavigationParser(__DIR__ . '/menu');
$menu = $fs_parser->parse();
$socket = new NavigableSocket\NavigableSocket('127.0.0.1', 23, 5, $menu);
$socket->loop();
