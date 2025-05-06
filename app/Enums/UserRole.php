<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum UserRole: string
{
    use EnumHelper;

    case GUEST = 'guest';
    case USER = 'user';
    case HOST = 'host';
    case ADMIN = 'admin';
}
