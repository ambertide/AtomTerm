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
 * Special characters for VT102 characterset.
 */
enum Charset: string {
    case BORDER_TOP_LEFT = "╒";
    case BORDER_VERTICAL = "│";
    case BORDER_BOTTOM_LEFT = "╘";
    case BORDER_TOP_RIGHT = "╕";
    case BORDER_BOTTOM_RIGHT = "╛";
    case BORDER_HORIZONTAL = "═";
}