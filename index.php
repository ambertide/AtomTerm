<?php

$loader = require __DIR__ . '/vendor/autoload.php';

$fs_parser = new \FSNavigation\NavigationParser(__DIR__ . '/menu');
$menu = $fs_parser->parse();
$socket = new NavigableSocket\NavigableSocket('0.0.0.0', 23, 5, $menu);
$socket->loop();
