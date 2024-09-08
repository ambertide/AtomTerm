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
 * Stateful class used to create renderings of a screen.
 */
class ScreenRenderer {
    
    private int $current_x_offset = 0;
    private int $current_y_offset = 0;
    private string $render = '';

    public function __construct(
        readonly private int $width,
        readonly private int $height
    ) { }

    /**
     * Add header to the render.
     * @param string $title title of the screen
     * @return void
     */
    private function set_header(string $title) {
        $this->render .= Charset::BORDER_TOP_LEFT->value .
            str_repeat(Charset::BORDER_HORIZONTAL->value, 4) .
            " $title " .
            str_repeat(Charset::BORDER_HORIZONTAL->value, $this->width - (8 + mb_strlen($title))) .
            Charset::BORDER_TOP_RIGHT->value . PHP_EOL;
        $this->current_y_offset++;
    }

    /**
     * Add footer to the render.
     * @return void
     */
    private function set_footer() {
        $this->render .= Charset::BORDER_BOTTOM_LEFT->value .
            str_repeat(Charset::BORDER_HORIZONTAL->value, $this->width - 2) .
            Charset::BORDER_BOTTOM_RIGHT->value;
    }

    /**
     * Get number of characters to the end of the line.
     * @return int number of characters to the end of the line.
     */
    private function chars_to_end_of_line(): int {
        return $this->width - $this->current_x_offset - 1;
    }

    /**
     * Fill the render until the end of the current line.
     * @return void
     */
    private function fill_to_end_of_line() {
        if ($this->current_x_offset === 0) {
            $this->render .= Charset::BORDER_VERTICAL->value . ' ';
            $this->current_x_offset = 2;
        }
        
        if ($this->chars_to_end_of_line() > 2) {
            $this->render .= str_repeat(' ', $this->chars_to_end_of_line());
        }
        $this->render .= Charset::BORDER_VERTICAL->value;
        $this->render .= PHP_EOL;
        $this->current_x_offset = 0;
        $this->current_y_offset++;
    }

    /**
     * Fill the render until the end of the file.
     * @return void
     */
    private function fill_empty_lines_until_end() {
        $lines_left_in_screen = $this->height - $this->current_y_offset - 1;
        $empty_line =  Charset::BORDER_VERTICAL->value .
            str_repeat(' ', $this->width - 2) .
            Charset::BORDER_VERTICAL->value .
            PHP_EOL;
        $this->render .= str_repeat($empty_line, $lines_left_in_screen);
        $this->current_x_offset = 0;
        $this->current_y_offset = $this->height - 1;
    }

    private function words(string $text): array {
        return explode(' ', $text);
    }

    /**
     * Add a word to the screen. Update the offsets.
     * @param string $word Word to add.
     * @return void
     */
    private function add_word(string $word): void {
        if ($this->current_x_offset === 0) {
            $this->render .= Charset::BORDER_VERTICAL->value . ' ';
            $this->current_x_offset = 2;
        }

        $this->render .= $word . ' ';
        $this->current_x_offset += mb_strlen($word) + 1;
    }

    /**
     * Reset the screen renderer.
     * @return void
     */
    public function reset() {
        $this->current_x_offset = 0;
        $this->current_y_offset = 0;
        $this->render = '';
    }

    /**
     * Create a the text of the screen from a given
     * text.
     * @param string $text Text to put into borders.
     * @return string
     */
    public function create_screen_from_text(
        string $text,
        string $title
    ): string {
        $this->set_header($title);
        $lines = explode(PHP_EOL, $text);
        foreach ($lines as $line) {
            $words = $this->words($line);
            foreach ($words as $word) {
                if ($this->current_y_offset === ($this->height - 1)) {
                    // Fallback.
                    break;
                }

                if ($this->chars_to_end_of_line() < (mb_strlen($word) + 2)) {
                    // Skip to the next line if the word is too big for remaining space.
                    $this->fill_to_end_of_line();
                }

                $this->add_word($word);
            }
            // Finish the line before continuing.
            $this->fill_to_end_of_line();
        }

        if ($this->current_x_offset < ($this->width - 1)) {
            // If not at the end of the current line, finish that line.
            $this->fill_to_end_of_line();
        }

        if ($this->current_y_offset < ($this->height -1)) {
            // If not at the end of the file, finish almost to the
            // end.
            $this->fill_empty_lines_until_end();
        }
        $this->set_footer();
        return $this->render;
    }
}
