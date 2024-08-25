<?php

namespace Navigation;

/**
 * Handles the current textual representation,
 * as well event handling for menus and documents.
 */
class Handler {
    private Screen $root;
    private Screen $current;
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
        switch ($event) {
            case Event::NAV_BACK_KEY_EVENT:
                // Try to go back a screen if user clicked back.
                if ($this->current !== $this->root) {
                    $this->current = $this->current->parent();
                    return true;
                }
                break;
            case Event::NAV_ENTER_KEY_EVENT:
                if ($this->current instanceof Menu) {
                    // Proceed to the next document or menu.
                    $next_scene = $this->current->hovered_child();
                    $this->current = $next_scene;
                    return true;
                }
                break;
            default:
                if ($this->current instanceof Menu) {
                    return $this->current->process_menu_event($event);
                }
                break;
        }
        return false;
    }

    /**
     * Get what to render.
     * @return string string representation of the
     * current screen.
     */
    public function render() {
        return $this->current->render();
    }
}