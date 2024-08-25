<?php

namespace TelnetSocket;


class Socket
{
    private \Socket $socket;
    private int $backlogCount;
    private array $connected_sockets;


    /**
     * Construct a TelnetSocket.
     *
     * @param integer $port Port the socket is acting on.
     * @param integer $backlogCount Count of connections on backlog.
     */
    public function __construct(
        int $port,
        int $backlog_count
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
            '127.0.0.1',
            $port
        );
        print('Socket Bound' . PHP_EOL);
        $this->check_socket_error();             
        // Don't block on connections.
        socket_set_nonblock($this->socket);
        print('Socket listening...' . PHP_EOL);
        socket_listen($this->socket, $this->backlogCount);
        $this->check_socket_error();             
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
    public function process_connections(): \Generator
    {
        foreach ($this->connected_sockets as $socket) {
            $buffer = socket_read($socket, 2048, PHP_BINARY_READ);
            if ($buffer) {
                // Call the callback.
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
    public function accept_connections(): \Generator
    {
        do {
            $incoming_socket = @socket_accept($this->socket);
            if ($incoming_socket !== false) {
                print('Connection established' . PHP_EOL);
                // If there is an incoming socket register it.
                socket_set_nonblock($incoming_socket);
                $this->connected_sockets[] = $incoming_socket;
            }
            $this->check_socket_error();             
            yield $incoming_socket;
        } while (true);
    }
}
