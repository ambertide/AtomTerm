<?php

$loader = require __DIR__ . '/vendor/autoload.php';

$laika = new Navigation\Document(
    "First dog in space, sent on Sputnik 2 by the USSR.",
    "Laika"
);

$venera = new Navigation\Document(
    "Soviet space programs aimed at exploration of Venus, this unmanned probes were sent by USSR.",
    "Venera"
);

$menu = new Navigation\Menu(
    "This terminal includes information about the Soviet Space program.",
    "SpaceY"
);

$menu->add_child($venera);
$menu->add_child($laika);

$socket = new NavigableSocket\NavigableSocket('127.0.0.1', 23, 5, $menu);
$socket->loop();
