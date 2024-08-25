<?php

namespace Navigation;

abstract class Screen {
    /** Screen we just came from. */
    protected Screen $parent;
    private ScreenType $type;
    private string $title;

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
}