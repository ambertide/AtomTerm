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

namespace Navigation;

abstract class Screen {
    /** Screen we just came from. */
    protected Screen $parent;
    private ScreenType $type;
    private string $title;

    private string $_id;

    /**
     * Generate a navigation screen, which can be displayed in a
     * user's terminal.
     * 
     * @param \Navigation\ScreenType $type Type of the screen.
     * @param \Navigation\Screen|null $parent Screen this screen
     * had came from.
     */
    public function __construct(
        ScreenType $type,
        string $title,
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->_id = bin2hex(random_bytes(12));
    }

    /**
     * Get the string representation of the screen.
     * @return string What to draw on the terminal, essantially.
     */
    abstract public function render(): string;

    /**
     * Get the title of this screen.
     * @return string
     */
    public function title(): string {
        return $this->title;
    }

    /**
     * Get the parent of this screen, or null
     * if no such parent exists.
     * @return \Navigation\Screen
     */
    public function parent(): Screen {
        return $this->parent;
    }

    /**
     * Get the id of the screen.
     * @return string
     */
    public function id(): string {
        return $this->_id;
    }
}