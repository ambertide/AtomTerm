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
