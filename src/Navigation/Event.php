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

enum Event {
    case NAV_LEFT_KEY_EVENT;
    case NAV_RIGHT_KEY_EVENT;
    case NAV_UP_KEY_EVENT;
    case NAV_DOWN_KEY_EVENT;
    case NAV_ENTER_KEY_EVENT;
    case NAV_BACK_KEY_EVENT;
    case NAV_INCOMPREHENSIBLE_EVENT;
}
