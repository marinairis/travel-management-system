<?php

declare(strict_types=1);

namespace App\Exceptions\User;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserException extends Exception
{
    public const NOT_FOUND           = 'user.not_found';
    public const UNAUTHORIZED        = 'user.unauthorized';
    public const CANNOT_DELETE_SELF  = 'user.cannot_delete_self';
    public const CANNOT_DISABLE_SELF = 'user.cannot_disable_self';

    public function getStatusCode(): int
    {
        return $this->code ?: Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
