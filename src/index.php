<?php

include_once 'NavigableSocket.php';

$socket = new NavigableSocket\NavigableSocket('127.0.0.1', 23, 5);
$socket->loop();
