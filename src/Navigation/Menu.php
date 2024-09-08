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

/**
 * A menu is a type of screen that includes
 * clickable paths to other screens.
 */
class Menu extends Screen {
    private string $description;
    private array $children;

    private int $hovered_index = 0;

    /**
     * Create a menu instance.
     *
     * @param \Navigation\Screen|null $parent
     * @param string $description Summary on top of the menu
     */
    public function __construct(
        string $description,
        string $title
    ) {
        parent::__construct(
            ScreenType::MENU,
            $title
        );

        $this->description = $description;
        $this->children = [];
    }

    public function render(): string {
        $text = ($this->description ?? '') . PHP_EOL . PHP_EOL;
        foreach ($this->children as $index => $child) {
            // Add each child as a line.
            $text .= "* [" . $child->title() . "]";
            if ($index === $this->hovered_index) {
                $text .= "*";
            }
            $text .= PHP_EOL;
        }
        return $text;
    }

    /**
     * Process user events for this menu.
     * @param \Navigation\Event $event Event by the user.
     * @return bool true if redraw is necessary.
     */
    public function process_menu_event(Event $event) {
        switch ($event) {
            case Event::NAV_UP_KEY_EVENT:
                $this->hovered_index = max($this->hovered_index - 1, 0);
                return true;
            case Event::NAV_DOWN_KEY_EVENT:
                $this->hovered_index = min($this->hovered_index + 1, count($this->children) - 1);
                return true;
            default:
                return false;
        }
    }

    /**
     * Return the screen representing the currently
     * hovered option.
     * @return Screen
     */
    public function hovered_child(): Screen {
        return $this->children[$this->hovered_index];
    }

    /**
     * Add a screen as a child to this screen.
     * @param \Navigation\Screen $screen Screen to add.
     * @return void
     */
    public function add_child(Screen $screen) {
        $this->children[] = $screen;
        $screen->parent = $this;
    }
}
