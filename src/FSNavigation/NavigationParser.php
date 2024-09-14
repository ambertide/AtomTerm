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

namespace FSNavigation;

/**
 * Parses a directory as \Navigation\Menu object.
 */
class NavigationParser {
    private string $base_directory;

    public function __construct(string $base_directory) {
        $this->base_directory = $base_directory;
    }

    /**
     * Parse an individual document.
     * @param string $path Path to the affromentioned document.
     * @return \Navigation\Document Document to parse.
     */
    private function parse_document(string $path): \Navigation\Document {
        try {
            error_log("Parsing $path");
            $contents = file_get_contents($path);
            $data = explode(PHP_EOL, $contents, 2);
            if (count($data) === 2) {
                [$title, $text] = $data;
            } else {
                $title = basename($path);
                $text = $contents;
            }
            return new \Navigation\Document(
                $text,
                $title
            );
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new \Navigation\Document(
                'Corrupted file',
                'Corrupted'
            );
        }
    }

    /**
     * Parse a menu metadata file
     *
     * @param string $path Path to parse.
     * @return \Navigation\Menu|false false if file cannot
     * be parsed, or the menu file if parsed.
     */
    private function parse_meta(string $path) {
        try {
            error_log("Parsing $path");
            $contents = file_get_contents($path);
            $metadata = json_decode($contents);
            if ($metadata) {
                return new \Navigation\Menu(
                    $metadata->description,
                    $metadata->title
                );
            } else {
                return false;
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Parse a directory as a menu, recursivelly.
     * @param string $path menu path
     * @return \Navigation\Menu a menu object containing the directory contents.
     */
    private function parse_menu(string $path): \Navigation\Menu {
        error_log("Parsing $path");
        $menu_directory_iterator = new \FilesystemIterator(
            $path,
            \FilesystemIterator::SKIP_DOTS
            | \FilesystemIterator::CURRENT_AS_FILEINFO
        );

        $possible_meta_file = $path . DIRECTORY_SEPARATOR . 'meta.json';
        if (file_exists($possible_meta_file)) {
            // If a metadata file exists, get the data about the menu from there.
            $menu = $this->parse_meta($possible_meta_file);
        } else {
            // Otherwise init a default one.
            $menu = new \Navigation\Menu(
                '',
                'MISSING'
            );
        }

        foreach ($menu_directory_iterator as $file_or_dir) {
            if ($file_or_dir->getFilename() === 'meta.json') {
                // Already parsed.
                continue;
            }
            
            if ($file_or_dir->isFile()) {
                $menu->add_child($this->parse_document($file_or_dir->getPathname()));
            } else if ($file_or_dir->isDir()) {
                $menu->add_child($this->parse_menu($file_or_dir->getPathname()));
            }
        }

        return $menu;
    }

    /**
     * Parse the directory set as root as a menu item.
     * @return \Navigation\Menu parsed manu object.
     */
    public function parse(): \Navigation\Menu {
        return $this->parse_menu($this->base_directory);
    }
}