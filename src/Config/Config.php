<?php

namespace Config;

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

/**
 * Application configuration.
 */
class Config {
    // Initial config with defaults.
    private array $config = [
        'host' => '0.0.0.0',
        'port' => 23,
        'root' => 'menu'
    ];


    /**
     * Set the config depending on a config file.
     * @param string $config_path Path to the file,
     * if not provided, defaults to config.json.
     */
    function __construct(string $config_path = 'config.json') {
        try {
            $contents = file_get_contents($config_path);
            $config_data = json_decode($contents, true);
            if (!$config_data) {
                throw new \Exception('Invalid JSON');
            }
            foreach ($this->config as $config_key => $default) {
                // If config does not override a key, set its default.
                if (array_key_exists($config_key, $config_data)) {
                    $this->config[$config_key] = $config_data[$config_key];
                } else {
                    $this->config[$config_key] = $default;
                }
            }
        } catch (\Exception $e) {
            error_log('Error while setting config, defaulting to default values.');
        }
    }

    /**
     * Port the server is served from.
     * @return int Port number.
     */
    public function port(): int {
        return $this->config['port'];
    }

    /**
     * Document root for the menus.
     * @return string Root of the document.
     */
    public function root(): string {
        return $this->config['root'];
    }

    /**
     * Host the server is running on.
     * @return string host address.
     */
    public function host(): string {
        return $this->config['host'];
    }
}
