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

namespace TelnetSocket;

/**
 * Holds commands for option negotiation
 * with the Telnet client.
 */
enum Command: int {
    case IAC = 255;
    case WILL = 251;
    case WONT = 252;
    case DO = 253;
    case DONT = 254;
    case SB = 250;
    case SE = 240;
    case NAWS = 31;
    case LINEMODE = 34;
    case ECHO = 1;

    // Are you there;
    case AYT = 246;

    /**
     * Encode a group of decoded commands
     *
     * @param integer ...$values Decoded command values.
     * @return string Encoded command bytestring.
     */
    private static function convert(int ...$values): string {
        $output = '';
        foreach ($values as $value) {
            $output .= pack('C*', $value);
        }
        return $output;
    }

    /**
     * Chain this command with a list of other commands
     * and return the resulting command string.
     */
    public function and(Command ...$commands): string {
        $all_commands = [$this, ...$commands];
        $command_values = [];
        foreach ($all_commands as $command) {
            $command_values[] = $command->value;
        }
        return self::convert(...$command_values);
    }

    /**
     * Create a bytestring representing a command subnegotation.
     * @param int[] $args Arguments of subnegotiation.
     * @return string Bytestring representing command subnegotiation.
     */
    public static function subnegotiate(int ...$args): string {
        $commands = [
            self::IAC->value,
            self::SB->value,
            ...$args,
            self::IAC->value,
            self::SE->value
        ];
        return self::convert(...$commands);
    }

    /**
     * Convert bytestring to readable commands.
     * @param string $payload payload to convert.
     * @return string Converted to commands
     */
    public static function decode(string $payload): string {
        $commands = [];
        $payload_chars = unpack('C*', $payload);
        foreach ($payload_chars as $command_character) {
            $commands[] = self::tryFrom($command_character)->name ?? 'UNDEF';
        }
        return implode(' ', $commands);
    }
}
