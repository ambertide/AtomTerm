<?php

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