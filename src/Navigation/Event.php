<?php

namespace Navigation;

enum Event {
    case NAV_LEFT_KEY_EVENT;
    case NAV_RIGHT_KEY_EVENT;
    case NAV_UP_KEY_EVENT;
    case NAV_DOWN_KEY_EVENT;
    case NAV_ENTER_KEY_EVENT;
    case NAV_BACK_KEY_EVENT;
}
