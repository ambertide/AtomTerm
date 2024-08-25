<?php

namespace Navigation;

/**
 * A document is a type of string that only includes a
 * readable snippet of information.
 */
class Document extends Screen {
    private string $content;

    public function __construct(
        string $content,
        string $title
    ) {
        parent::__construct(
            ScreenType::DOCUMENT,
            $title
        );

        $this->content = $content;
    }

    public function render(): string {
        return $this->content ?? '';
    }
}
