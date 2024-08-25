<?php

namespace Navigation;

/**
 * A document is a type of string that only includes a
 * readable snippet of information.
 */
class Document extends Screen {
    private string $content;

    public function __construct(
        Screen|null $parent = null,
        string $content
    ) {
        parent::__construct(
            ScreenType::DOCUMENT,
            $parent
        );

        $this->content = $content;
    }

    public function render(): string {
        return $this->content ?? '';
    }
}
