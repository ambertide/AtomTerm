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
 * Handles the current textual representation,
 * as well event handling for menus and documents.
 */
class Handler {
    private Screen $root;
    private Screen $current;
    private bool $rendered_once = false;

    public function __construct(Screen $root) {
        $this->root = $root;
        $this->current = $root;
    }

    /**
     * Process a user event and check if we should redraw the
     * screen.
     * @param \Navigation\Event $event Event to process.
     * @return bool true if redraw is necessary.
     */
    public function proccess_event(Event $event): bool {
        if (!$this->first_render_occurred()) {
            // Do not process events if the first render has
            // not occurred yet.
            return true;
        }
        switch ($event) {
            case Event::NAV_BACK_KEY_EVENT:
                // Try to go back a screen if user clicked back.
                if ($this->current->id() !== $this->root->id()) {
                    $this->current = $this->current->parent();
                    $should_rerender = true;
                } else {
                    $should_rerender = false;
                }
                break;
            case Event::NAV_ENTER_KEY_EVENT:
                if ($this->current instanceof Menu) {
                    // Proceed to the next document or menu.
                    $next_scene = $this->current->hovered_child();
                    $this->current = $next_scene;
                    $should_rerender = true;
                } else {
                    $should_rerender = false;
                }
                break;
            default:
                if ($this->current instanceof Menu) {
                    $should_rerender = $this->current->process_menu_event($event);
                } else {
                    $should_rerender = false;
                }
                break;
        }
        return $should_rerender;
    }

    /**
     * Get what to render.
     * @param int $width width of the canvas.
     * @param int $height height of the canvas.
     * @return string string representation of the
     * current screen.
     */
    public function render(int $width = 80, int $height = 20) {
        $screen_renderer = new ScreenRenderer($width, $height);
        return $screen_renderer->create_screen_from_text(
            $this->current->render(),
            $this->current->title()
        );
    }

    /**
     * Signal that the first render has occurred.
     * @return void
     */
    public function first_render_has_occurred() {
        $this->rendered_once = true;
    }

    /**
     * Get if the first render have occurred.
     * @return bool
     */
    public function first_render_occurred() {
        return $this->rendered_once;
    }
}