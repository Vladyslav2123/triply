<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum ReservationStatus: string
{
    use EnumHelper;

    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PAID = 'paid';
    case CANCELLED_BY_GUEST = 'cancelled_by_guest';
    case CANCELLED_BY_HOST = 'cancelled_by_host';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';
    case REFUNDED = 'refunded';
}
