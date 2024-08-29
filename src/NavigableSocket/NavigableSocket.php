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
     * Bind the nav handler to established connection.
     */
    protected function on_connect(\TelnetSocket\Connection $connection) {
        parent::on_connect($connection);
        $navigation_handler = $this->bind_nav_handler($connection);
        $connection->write(
            $navigation_handler->render()
        );
    }

    /**
     * Process a newly arriving message and clear the user screen.
     */
    protected function on_message_recieved(
        string $message,
        \TelnetSocket\Connection $connection
    ) {
        parent::on_message_recieved($message, $connection);
        $navigation_handler = $this->get_nav_handler($connection);
        $event = TerminalUtils::convert_message_to_event($message);
        // Send the next event to the navigation handler.
        $should_rerender = $navigation_handler->proccess_event($event);
        if ($should_rerender) {
            $connection->clear_screen();
            $text = $navigation_handler->render();
            $characters = mb_str_split($text);
            foreach($characters as $char) {
                if ($char === PHP_EOL) {
                    $connection->write(pack('c*', 12, 12));
                } else {
                    $connection->write($char);
                }
            }
        }
    }

    /**
     * Remove navigation handler for connection.
     */
    protected function on_close(\TelnetSocket\Connection $connection) {
        parent::on_close($connection);
        if ($this->get_nav_handler($connection)) {
            unset($this->navigation_handlers[$connection->id()]);
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