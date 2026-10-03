<?php

namespace App\Modules\Comments\Enums;

enum CommenterType: string
{
    case User = 'user';
    case Admin = 'admin';
    case Vendor = 'vendor';
}
