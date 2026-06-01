<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class AuthException extends DomainException
{
    public const INVALID_CREDENTIALS = 'auth.invalid_credentials';

    public const TOKEN_REFRESH_ERROR = 'auth.token_refresh_error';

    public const FORGOT_PASSWORD_ERROR = 'auth.forgot_password_error';

    public const RESET_PASSWORD_INVALID_TOKEN = 'auth.reset_password_invalid_token';

    public const RESET_PASSWORD_INVALID_USER = 'auth.reset_password_invalid_user';

    public const RESET_PASSWORD_THROTTLED = 'auth.reset_password_throttled';

    public const RESET_PASSWORD_ERROR = 'auth.reset_password_error';

    public function getStatusCode(): int
    {
        return $this->code ?: Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
