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
            30
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
        $connection->clear_screen();
        $this->bind_nav_handler($connection);
        error_log('Nav handler count: ' . count($this->navigation_handlers));
        $connection->write('Setting up connection...');
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
        if ($connection->properties()->initialized) {
            // Wait for terminal properties to be fetched.
            $should_rerender = $navigation_handler->proccess_event($event);
            if (!$navigation_handler->first_render_occurred()) {
                $navigation_handler->first_render_has_occurred();
            }
            if ($should_rerender) {
                $connection->clear_screen();
                $connection->write_text($navigation_handler->render(
                    $connection->properties()->w,
                    $connection->properties()->h
                )); 
            } 
        } else {
            $connection->write('Setting up connection...');
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