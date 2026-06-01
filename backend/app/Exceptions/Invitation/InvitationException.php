<?php

declare(strict_types=1);

namespace App\Exceptions\Invitation;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class InvitationException extends DomainException
{
    public const NOT_FOUND = 'invitation.not_found';

    public const EXPIRED = 'invitation.expired';

    public const ALREADY_USED = 'invitation.already_used';

    public const EMAIL_FAILED = 'invitation.email_failed';

    public function getStatusCode(): int
    {
        return $this->code ?: Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
