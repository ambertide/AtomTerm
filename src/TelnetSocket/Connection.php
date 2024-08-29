<?php

namespace TelnetSocket;

/**
 * Telnet compatible terminal
 * user is connecting from.
 */
class Connection {
    private \Socket $socket;
    private int $buffer_length;

    private string $_id;

    public function __construct(\Socket $socket, int $buffer_length) {
        socket_set_nonblock($socket);
        $this->socket = $socket;
        $this->buffer_length = $buffer_length;
        $this->_id = random_bytes(12);
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

    /**
     * Get the unique id of the socket.
     * @return string Get the id of the socket.
     */
    public function id(): string {
        return $this->_id;
    }
}
