<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum Ticket: string
{
    use EnumHelper;

    case EVENT = 'event_ticket';
    case SHOW = 'show_ticket';
    case ENTRY = 'entry_ticket';
    case ENTRANCE = 'entrance_fee';
    case OTHER = 'other';
}
