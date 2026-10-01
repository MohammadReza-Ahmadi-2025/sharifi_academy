<?php

namespace App\Modules\Auth\Enums;

enum OtpType: string
{
    case Login = 'login';
    case Register = 'register';
    case ResetPassword = 'reset_password';
}
