<?php

/* AtomTerm
Copyright (C) 2024  Ege Özkan

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>. */

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

    private TerminalProperties $terminal_properties;

    /**
     * If filled, waiting for an answer from the telnet connection,
     * run the callback when it is fired.
     * @var \Closure function to run on answer.
     */
    private \Closure|null $on_telnet_answer;

    public function __construct(\Socket $socket, int $buffer_length) {
        socket_set_nonblock($socket);
        $this->socket = $socket;
        $this->buffer_length = $buffer_length;
        $this->_id = bin2hex(random_bytes(12));
        $this->created_at = time();
        $this->last_message_timestamp = $this->created_at;
        $this->ayt_sent = false;
        $this->flush_read_buffer();
        $this->determine_terminal_properties();
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
     * Move cursor in the terminal.
     *
     * @return void
     */

    private function move_cursor(int $x_offset, int $y_offset) {
        $this->write(
            "\e[" .
            $x_offset .
            ';' .
            $y_offset . 'H'
        );
    }

    /**
     * Ask terminal client of the connection
     * to clear itself.
     * @return void
     */
    public function clear_screen() {
        $this->write("\e[2J");
        // Move cursor to top of screen.
        $this->move_cursor(0, 0);
    }

    /**
     * Read 16-bit and convert it into a number.
     * @return int
     */
    private function read_short_raw(): int {
        try {
            $message = socket_read(
                $this->socket,
                2,
                PHP_BINARY_READ
            );
            $number = array_values(unpack('n', $message));
            if (count($number) > 0) {
                return $number[0];
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Read from the socket connection.
     * @return string message, empty string if no message.
     */
    public function read(): string|bool {
        socket_clear_error();
        $message = socket_read(
            $this->socket,
            $this->buffer_length,
            PHP_BINARY_READ
        );

        if ($message) {
            if ($this->on_telnet_answer && $message[0] === "\xFF") {
                // If on raport callback attached, call and unset.
                $raport_sucessful = $this->on_telnet_answer->call($this, $message);
                if ($raport_sucessful) {
                    $this->on_telnet_answer = null;
                    $this->last_message_timestamp = time();
                    return '';
                }
            }
            // Unset hang up commmiters.
            $this->ayt_sent = false;
            $this->last_message_timestamp = time();
            return $message;
        } else if ($message === false) {
            $last_error = socket_last_error($this->socket);
            if ($last_error !== 35 && $last_error !== 11) {
                // Last error is 35 (Mac OS) or 11 (Linux) when socket has no incoming message.
                error_log("Socket failed with unexpected error or closed by peer with code " . $last_error);
                // Signal close.
                return false;
            }
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
     * Close the connection to the underlying socket.
     */
    public function close(): void {
        echo socket_last_error($this->socket);
        socket_close($this->socket);
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
        $elapsed_time = $now - $this->last_message_timestamp;
        if ($elapsed_time > $timeout) {
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

    /**
     * Flush the input buffer from the terminal.
     * @return void
     */
    private function flush_read_buffer() {
        while (($this->read()) !== '' && (time() - $this->created_at) > 1) {}
    }

    /**
     * Send a query to a TELNET client and register a callback
     * for when the answer is returned. 
     * @param string $query Query to send.
     * @param \Closure $callback Callback to execute on
     * new messages, if the query returns true, it will
     * no longer be called.
     * @return void
     */
    private function telnet_negotiate(
        string $query,
        \Closure $callback
    ) {
        // Set up the callback for a query.
        $this->on_telnet_answer = $callback;
        // Fire the query.
        $this->write($query);
    }

    /**
     * Determine size and properties of the terminal connecting.
     * @return void
     */
    private function determine_terminal_properties() {
        $term_props = new TerminalProperties();
        $this->terminal_properties = $term_props; 
        $this->telnet_negotiate(
            // Ask TELNET client to use RFC1073 Window Size Option
            Command::IAC->and(
                Command::DO,
                Command::NAWS
            ),
            function (string $message) use (&$term_props) {
                try {
                    $message = Command::decode($message);
                    // When tellnet starts subnegotiating
                    // capture it and extract the window sizes.
                    if ($message === 'IAC SB NAWS') {
                        $term_props->w = $this->read_short_raw();
                        $term_props->h = $this->read_short_raw();
                        $term_props->initialized = true;
                        return true;
                    } else if ($message === 'IAC WONT NAWS') {
                        // This means TELNET client doesn't use NAWS.
                        $term_props->w = 80;
                        $term_props->h = 80;
                        $term_props->initialized = true;
                        return true;
                    }
                    return false;
                } catch (\Exception $e) {
                    $term_props->w = 80;
                    $term_props->h = 80;
                    $term_props->initialized = true;
                    error_log('Raport failed with ' . $e->getMessage());
                    return false;
                } 
            }
        );
    } 
    /**
    * Properties of the terminal in the client.
    *
    * @return TerminalProperties Properties of the terminal
    */
    public function properties(): TerminalProperties {
        return $this->terminal_properties;
    }
}
