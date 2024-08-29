<?php

namespace NavigableSocket;

/**
 * Used to create a Socket Server that can be
 * traversed using keyboard.
 */
class NavigableSocket extends \TelnetSocket\Socket {

    private \Navigation\Screen $rootScreen;
    private array $navigation_handlers;

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
        $this->rootScreen = $rootScreen;
        parent::__construct(
            $ip,
            $port,
            $backlog_count,
            3,
            120
        );
        $this->register_connection_callback(function (\TelnetSocket\Connection $conn) {
            $this->establish_connection($conn);
        });
        $this->register_message_callback(function (string $message, \TelnetSocket\Connection $conn) {
            $this->process_new_message($message, $conn);
        });
    }

    /**
     * Create a new navigation handler and bind
     * it to a connection.
     * @param \TelnetSocket\Connection $connection Connection client.
     * @return \Navigation\Handler Created and bound navigation
     * handler.
     */
    private function bind_nav_handler(
        \TelnetSocket\Connection $connection,
    ): \Navigation\Handler {
        $handler = new \Navigation\Handler($this->rootScreen);
        $this->navigation_handlers[$connection->id()] = $handler;
        return $handler;
    }

    /**
     * Get the navigation handler of a connection.
     * @param \TelnetSocket\Connection $connection Connection
     * to get the bound navigation handler for.
     * @return \Navigation\Handler Bound navigation handler.
     */
    private function get_nav_handler(
        \TelnetSocket\Connection $connection
    ): \Navigation\Handler {
        return $this->navigation_handlers[$connection->id()];
    }

    /**
     * Establish a connection and bind a navigation handler to it.
     * @param \TelnetSocket\Connection $connection Create a connection
     * @return void
     */
    private function establish_connection(
        \TelnetSocket\Connection $connection
    ) {
        $navigation_handler = $this->bind_nav_handler($connection);
        $connection->write(
            $navigation_handler->render()
        );
        error_log('Connection established.');
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
        $navigation_handler = $this->get_nav_handler($connection);
        $event = TerminalUtils::convert_message_to_event($message);
        // Send the next event to the navigation handler.
        $should_rerender = $navigation_handler->proccess_event($event);
        if ($should_rerender) {
            $connection->clear_screen();
            $connection->write($navigation_handler->render());
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