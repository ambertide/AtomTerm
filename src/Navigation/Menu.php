<?php

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
                $this->hovered_index = min($this->hovered_index - 1, 0);
                return true;
            case Event::NAV_DOWN_KEY_EVENT:
                $this->hovered_index = min($this->hovered_index + 1, count($this->children));
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
