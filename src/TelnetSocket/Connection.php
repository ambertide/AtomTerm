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
    private int $created_at;
    private int $last_message_timestamp; 
    private bool $ayt_sent;

    public function __construct(\Socket $socket, int $buffer_length) {
        socket_set_nonblock($socket);
        $this->socket = $socket;
        $this->buffer_length = $buffer_length;
        $this->_id = bin2hex(random_bytes(12));
        $this->created_at = time();
        $this->last_message_timestamp = $this->created_at;
        $this->ayt_sent = false;
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
        $message = socket_read(
            $this->socket,
            $this->buffer_length,
            PHP_BINARY_READ
        );
        if ($message) {
            // Unset hang up commmiters.
            $this->ayt_sent = false;
            $this->last_message_timestamp = time();
            return $message;
        }
        return '';
    }

    /**
     * Get the unique id of the socket.
     * @return string Get the id of the socket.
     */
    public function id(): string {
        return $this->_id;
    }

    /**
     * Check if a certain number of seconds
     * have passed since the socket last received a update,
     * if it has, ask the client if it is still there,
     * if not, close the connection and run on_close.
     * @param int $timeout Number of seconds to close
     * the socket after the last message and if it is not
     * open.
     * @return bool
     */
    public function close_if_timed_out(int $timeout) {
        $now = time();
        $elapsed_time = $now - $timeout;
        if (($now - $elapsed_time) > $timeout) {
            if ($this->ayt_sent) {
                // If AYT already sent without answer
                // close connection.
                socket_close($this->socket);
                return true;
            }
            // Send AYT to check for its health.
            $are_you_there = Command::IAC->and(Command::AYT);
            $this->write($are_you_there);
            $this->ayt_sent = true;
        }
        return false;
    }

    /**
     * Write text to the screen one character at a time.
     * @param string $text Text to write.
     * @return void
     */
    public function write_text(string $text) {
        $characters = mb_str_split($text);
        foreach($characters as $char) {
            if ($char === PHP_EOL) {
                $this->write("\n\r");
            } else {
                $this->write($char);
            }
        }
    }
}
