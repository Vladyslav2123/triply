<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum PaymentMethod: string
{
    use EnumHelper;

    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';
    case BANK_TRANSFER = 'bank_transfer';
}
