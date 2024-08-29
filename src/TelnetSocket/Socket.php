<?php

namespace TelnetSocket;

/**
 * Raw socket client for TCP connections.
 */
class Socket
{
    private \Socket $socket;
    private int $backlogCount;
    private int $buffer_length;
    private array $connections;

    private int $timeout;

    private bool $should_close = false;


    /**
     * Construct a TelnetSocket.
     *
     * @param integer $port Port the socket is acting on.
     * @param integer $backlog_count Count of connections on backlog.
     * @param integer $buffer_length number of bytes to read.
     * @param integer $timeout number of seconds to pass without
     * reply for a socket to be closed.
     */
    public function __construct(
        string $ip,
        int $port,
        int $backlog_count,
        int $buffer_length,
        int $timeout
    ) {
        $this->backlogCount = $backlog_count;
        $this->connections = [];
        error_log('Establish socket...');
        $this->socket = socket_create(
            AF_INET,
            SOCK_STREAM,
            SOL_TCP
        );
        $this->check_socket_error();             
        error_log('Binding socket...');
        socket_bind(
            $this->socket,
            $ip,
            $port
        );
        error_log('Socket Bound');
        $this->check_socket_error();             
        // Don't block on connections.
        socket_set_nonblock($this->socket);
        error_log('Socket listening...');
        socket_listen($this->socket, $this->backlogCount);
        $this->check_socket_error();
        $this->buffer_length = $buffer_length;
        $this->timeout = $timeout;
    }

    /**
     * Callback to run after a connection is established
     * @param \TelnetSocket\Connection $connection Established connection.
     * @return void
     */
    protected function on_connect(Connection $connection) {
        error_log('Connection established to ' . $connection->id());
    }

    /**
     * Callback to run after a message is received.
     * @param string $message message to process
     * @param Connection $connection Connection the message arriving from.
     * @return void
     */
    protected function on_message_recieved(string $message, Connection $connection) {
        error_log('Recieved message from ' . $connection->id());
    }

    /**
     * Callback to run after a connection is closed.
     * @param Connection $connection connection that was just closed.
     * @return void
     */
    protected function on_close(Connection $connection) {
        error_log('Connection closed to ' . $connection->id());
    }

    /**
     * Checks errors after a socket call and clears the
     * error buffers, if an error do exist, log it.
     *
     * @param mixed $socket Socket to check errors for,
     * by default the pending connection socket.
     * @return void
     */
    private function check_socket_error($socket = null): void {
        if ($socket === null) {
            $socket = $this->socket;
        }
        $last_error = socket_last_error($this->socket);
        if ($last_error) {
            print(socket_strerror($last_error) . PHP_EOL);
        }
        socket_clear_error();
    }

    /**
     * Process already established connections.
     * @return \Generator returns if there is a new message.
     */
    protected function process_connections(): \Generator
    {
        foreach ($this->connections as $conn) {
            $connection_closed = $conn->close_if_timed_out($this->timeout);
            if ($connection_closed) {
                // If connection is done, delete it from connections
                // array and then call on_close callback if it exists.
                unset($this->connections[$conn->id()]);
                $this->on_close($conn);
                yield false;
            } else {
                // Otherwise read a message.
                $buffer = $conn->read();
                if ($buffer) {
                    $this->on_message_recieved($buffer, $conn);
                    yield true;
                } else {
                    yield false;
                }
            }
        }
    }

    /**
     * Check for incoming connections, if none arrives
     * yield. If one does arrive, register it.
     *
     * @return \Generator Waits for new connections.
     */
    protected function accept_connections(): \Generator
    {
        $incoming_socket = @socket_accept($this->socket);
        if ($incoming_socket !== false) {
            // If there is an incoming socket register it.
            $connection = new Connection($incoming_socket, $this->buffer_length);
            $this->option_negotiation($connection);
            $this->connections[$connection->id()] = $connection;
            $this->on_connect($connection);
        }
        $this->check_socket_error();             
        yield $incoming_socket;
    }

    /**
     * Negotiate with the Telnet client over which
     * options to use.
     * 
     * @return bool true if all options are accepted.
     */
    private function option_negotiation(Connection $conn): bool {
        // https://stackoverflow.com/a/4532395
        // explains this better than I do,
        // but sets the telnet to send me data directly.
        $order_linemode_neg = Command::IAC->and(
            Command::DO,
            Command::LINEMODE
        );
        $conn->write($order_linemode_neg);
        $turn_off_linemode = Command::subnegotiate(
            Command::LINEMODE->value,
            0
        );
        $conn->write($turn_off_linemode);
        $takeover_echo = Command::IAC->and(
            Command::WILL,
            Command::ECHO
        );
        $conn->write($takeover_echo);
        // Probably decode this at one point who knows.
        return true;
    }
}
