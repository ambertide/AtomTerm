<?php

namespace NavigableSocket;

/**
 * Used to create a Socket Server that can be
 * traversed using keyboard.
 */
class NavigableSocket extends \TelnetSocket\Socket {

    private \Navigation\Handler $navigation_handler;

    /**
     * Construct a navigable socket instance.
     *
     * @param string $ip IP of the socket.
     * @param int $port Port the socket resides in.
     * @param int $backlog_count Count of connections that
     * can reside in the backlog.
     */
    public function __construct(
        string $ip,
        int $port,
        int $backlog_count,
        \Navigation\Screen $rootScreen
    ) {
        parent::__construct(
            $ip,
            $port,
            $backlog_count,
            3
        );
        $this->navigation_handler = new \Navigation\Handler($rootScreen);
        $this->register_connection_callback(function (\TelnetSocket\Connection $conn) {
            $conn->clear_screen();
            $conn->write($this->navigation_handler->render());
            error_log('Connection established.');
        });
        $this->register_message_callback(function (string $message, \TelnetSocket\Connection $conn) {
            $this->process_new_message($message, $conn);
        });
    }

    /**
     * Process a newly arriving message and clear the user screen.
     * @param string $message message to process
     * @param \Socket $socket Socket the message arriving from.
     * @return void
     */
    private function process_new_message(
        string $message,
        \TelnetSocket\Connection $connection
    ) {
        $event = TerminalUtils::convert_message_to_event($message);
        // Send the next event to the navigation handler.
        $should_rerender = $this->navigation_handler->proccess_event($event);
        error_log('Should re-render is ' . ($should_rerender ? 'true' : 'false'));
        if ($should_rerender) {
            $connection->clear_screen();
            $connection->write($this->navigation_handler->render());
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