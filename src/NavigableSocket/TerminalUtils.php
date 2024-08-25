<?php

namespace NavigableSocket;

/**
 * Represents the user's terminal, connected via a telnet
 * socket.
 */
class TerminalUtils {

    private static $event_map = [
        "\e[A" => \Navigation\Event::NAV_UP_KEY_EVENT,
        "\e[B" => \Navigation\Event::NAV_DOWN_KEY_EVENT,
        "\e[C" => \Navigation\Event::NAV_RIGHT_KEY_EVENT,
        "\e[D" => \Navigation\Event::NAV_LEFT_KEY_EVENT,
        "\eE" => \Navigation\Event::NAV_ENTER_KEY_EVENT
    ];

    /**
     * Clear user's terminal.
     * @param \Socket $socket Socket to clear the screen from.
     */
    public static function clear(\Socket $socket) {
        socket_write($socket, "\e[2J");
    }

    /**
     * Convert raw message to \Navigation\Event instance.
     * @param string $message Raw message coming from the socket.
     * @return \Navigation\Event event representation of the message.
     */
    public static function convert_message_to_event(
        string $message
    ): \Navigation\Event {
        if (array_key_exists($message, self::$event_map)) {
            return self::$event_map[$message];
        }
        return \Navigation\Event::NAV_INCOMPREHENSIBLE_EVENT;
    }
}