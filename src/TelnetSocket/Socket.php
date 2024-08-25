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
    private array $connected_sockets;
    private \Closure $on_message;
    private \Closure $on_connection; 


    /**
     * Construct a TelnetSocket.
     *
     * @param integer $port Port the socket is acting on.
     * @param integer $backlogCount Count of connections on backlog.
     */
    public function __construct(
        string $ip,
        int $port,
        int $backlog_count,
        int $buffer_length
    ) {
        $this->backlogCount = $backlog_count;
        $this->connected_sockets = [];
        print('Creating socket...' . PHP_EOL);
        $this->socket = socket_create(
            AF_INET,
            SOCK_STREAM,
            SOL_TCP
        );
        $this->check_socket_error();             
        print('Binding socket...' . PHP_EOL);
        socket_bind(
            $this->socket,
            $ip,
            $port
        );
        print('Socket Bound' . PHP_EOL);
        $this->check_socket_error();             
        // Don't block on connections.
        socket_set_nonblock($this->socket);
        print('Socket listening...' . PHP_EOL);
        socket_listen($this->socket, $this->backlogCount);
        $this->check_socket_error();
        $this->buffer_length = $buffer_length;
    }

    /**
     * Register a callback function that gets triggered when
     * a new socket is connected.
     * @param \Closure $on_connection Given the newly connected socket
     * as its argument.
     * @return void
     */
    protected function register_connection_callback(\Closure $on_connection) {
        $this->on_connection = $on_connection;
    }

    /**
     * Register a callback to be called when a new message
     * is received.
     * @param \Closure $on_message A callback function that gets the
     * message and the socket as its argument.
     * @return void
     */
    protected function register_message_callback(\Closure $on_message) {
        $this->on_message = $on_message;
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
        foreach ($this->connected_sockets as $socket) {
            $buffer = socket_read($socket, $this->buffer_length, PHP_BINARY_READ);
            if ($buffer) {
                $this->on_message->call($this, $buffer, $socket);
                yield true;
            } else {
                yield false;
            }
        }
        yield false;
    }

    /**
     * Check for incoming connections, if none arrives
     * yield. If one does arrive, register it.
     *
     * @return \Generator Waits for new connections.
     */
    protected function accept_connections(): \Generator
    {
        do {
            $incoming_socket = @socket_accept($this->socket);
            if ($incoming_socket !== false) {
                // If there is an incoming socket register it.
                socket_set_nonblock($incoming_socket);
                $this->connected_sockets[] = $incoming_socket;
                $this->on_connection->call($this, $incoming_socket);
            }
            $this->check_socket_error();             
            yield $incoming_socket;
        } while (true);
    }
}
