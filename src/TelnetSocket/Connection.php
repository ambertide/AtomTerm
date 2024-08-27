<?php

namespace TelnetSocket;

/**
 * Telnet compatible terminal
 * user is connecting from.
 */
class Connection {
    private \Socket $socket;
    private int $buffer_length;

    public function __construct(\Socket $socket, int $buffer_length) {
        socket_set_nonblock($socket);
        $this->socket = $socket;
        $this->buffer_length = $buffer_length;
    }

    /**
     * Write to the connection.
     * @param string $data
     * @return void
     */
    public function write(string $data) {
        socket_write($this->socket, $data);
    } 

    /**
     * Ask terminal client of the connection
     * to clear itself.
     * @return void
     */
    public function clear_screen() {
        $this->write("\e[2J");
        // Move cursor to top of screen.
        $this->write("\e[0;0H");
    }

    public function read(): bool|string {
        return socket_read(
            $this->socket,
            $this->buffer_length,
            PHP_BINARY_READ
        );
    }
}
