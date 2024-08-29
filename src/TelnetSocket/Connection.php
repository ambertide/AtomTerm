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

    /**
     * If filled, waiting for an answer from the VT102 connection,
     * run the raport when it is fired.
     * @var \Closure function to run on answer.
     */
    private \Closure|null $on_raport;

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
    public function read(): string {
        $message = socket_read(
            $this->socket,
            $this->buffer_length,
            PHP_BINARY_READ
        );

        if ($message) {
            if ($this->on_raport && $message[0] === "\xFF") {
                // If on raport callback attached, call and unset.
                $raport_sucessful = $this->on_raport->call($this, $message);
                if ($raport_sucessful) {
                    $this->on_raport = null;
                    return '';
                }
            }
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
        while (($this->read()) !== "" && (time() - $this->created_at) > 1) {}
    }

    private function ask_raport(
        string $query,
        \Closure $callback
    ) {
        // Set up the callback for a query.
        $this->on_raport = $callback;
        // Fire the query.
        $this->write($query);
    }

    /**
     * Determine size and properties of the terminal connecting.
     * @return void
     */
    private function determine_terminal_properties() {
        $term_props = new TerminalProperties();
        $this->ask_raport(
            Command::IAC->and(
                Command::DO,
                Command::NAWS
            ),
            function (string $message) use (&$term_props) {
                try {
                    $message = Command::decode($message);
                    if ($message === 'IAC SB NAWS') {
                        $term_props->w = $this->read_short_raw();
                        $term_props->h = $this->read_short_raw();
                        var_dump($term_props); 
                    }
                    return false;
                } catch (\Exception $e) {
                    $term_props->w = 80;
                    $term_props->h = 80;
                    error_log('Raport failed with ' . $e->getMessage());
                    return false;
                } 
            }
        );
    } 
}
