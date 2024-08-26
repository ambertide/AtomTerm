<?php

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
        $contents = file_get_contents($path);
        [$title, $text] = explode(PHP_EOL, $contents, 2);
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
            error_log($path);
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
        $menu_directory_iterator = new \FilesystemIterator(
            $path,
            \FilesystemIterator::SKIP_DOTS
            | \FilesystemIterator::CURRENT_AS_FILEINFO
        );

        $possible_meta_file = $menu_directory_iterator->getPathname() . DIRECTORY_SEPARATOR . 'meta.json';
        if (file_exists($possible_meta_file)) {
            // If a metadata file exists, get the data about the menu from there.
            $menu = $this->parse_meta($possible_meta_file);
        } else {
            // Otherwise init a default one.
            $menu = new \Navigation\Menu(
                '',
                $menu_directory_iterator->getFilename()
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