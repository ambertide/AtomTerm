<?php


include "Socket.php";

$socket = new TelnetSocket\Socket(2024, 5);

foreach ($socket->accept_connections() as $newSocket) {
    foreach($socket->process_connections() as $hasMessage) {
    }
}
