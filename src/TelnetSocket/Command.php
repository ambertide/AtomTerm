<?php

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
    case LINEMODE = 34;
    case ECHO = 1;

    /**
     * Encode a group of decoded commands
     *
     * @param integer ...$values Decoded command values.
     * @return string Encoded command bytestring.
     */
    private static function convert(int ...$values): string {
        $output = '';
        foreach ($values as $value) {
            $output .= pack('c*', $value);
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
}
