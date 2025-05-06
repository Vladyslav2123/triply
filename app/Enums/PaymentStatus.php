<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum PaymentStatus: string
{
    use EnumHelper;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case DISPUTED = 'disputed';
}
