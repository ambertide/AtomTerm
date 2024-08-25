<?php

namespace NavigableSocket;

include_once 'Socket.php';
include_once 'NavigationEvent.php';


/**
 * Used to create a Socket Server that can be
 * traversed using keyboard.
 */
class NavigableSocket extends \TelnetSocket\Socket {

    /**
     * Construct a navigable socket instance.
     *
     * @param string $ip IP of the socket.
     * @param int $port Port the socket resides in.
     * @param int $backlog_count Count of connections that
     * can reside in the backlog.
     */
    public function __construct(string $ip, int $port, int $backlog_count) {
        parent::__construct(
            $ip,
            $port,
            $backlog_count,
            3
        );
        $this->register_connection_callback(function (\Socket $socket) {
            error_log('Connection established.');
        });
        $this->register_message_callback(function (string $message, \Socket $socket) {
            $this->process_new_message($message, $socket);
        });
    }

    /**
     * Process a newly arriving message and clear the user screen.
     * @param string $message message to process
     * @param \Socket $socket Socket the message arriving from.
     * @return void
     */
    private function process_new_message(string $message, \Socket $socket) {
        // Clear the screen.
        socket_write($socket, "\e[2J");
        $event = '';
        switch ($message) {
            case "\e[A":
                $event = NavigationEvent::NAV_UP_KEY_EVENT;
                break;
            case "\e[B":
                $event = NavigationEvent::NAV_DOWN_KEY_EVENT;
                break;
            case "\e[C":
                $event = NavigationEvent::NAV_RIGHT_KEY_EVENT;
                break;
            case "\e[D":
                $event = NavigationEvent::NAV_LEFT_KEY_EVENT;
                break;
            default:
                $event = null;
        }
    }

    /**
     * Generate a mainloop that accepts new connections
     * and then processes them.
     * @return never
     */
    public function loop() {
        do {
            $this->accept_connections()->next();
            $this->process_connections()->next();
        } while (true);
    }
}