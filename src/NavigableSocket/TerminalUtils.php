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
 * Represents the user's terminal, connected via a telnet
 * socket.
 */
class TerminalUtils {

    private static $event_map = [
        "\e[A" => \Navigation\Event::NAV_UP_KEY_EVENT,
        "\e[B" => \Navigation\Event::NAV_DOWN_KEY_EVENT,
        "\e[C" => \Navigation\Event::NAV_ENTER_KEY_EVENT,
        "\e[D" => \Navigation\Event::NAV_BACK_KEY_EVENT,
        "\eE" => \Navigation\Event::NAV_ENTER_KEY_EVENT,
        "\n" => \Navigation\Event::NAV_ENTER_KEY_EVENT,
        "\r" => \Navigation\Event::NAV_ENTER_KEY_EVENT,
        "\n\r" => \Navigation\Event::NAV_ENTER_KEY_EVENT,
        "\r\x00" => \Navigation\Event::NAV_ENTER_KEY_EVENT,
        ".\x00" => \Navigation\Event::NAV_BACK_KEY_EVENT
    ];

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