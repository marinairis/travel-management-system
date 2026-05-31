<?php

declare(strict_types=1);

namespace App\Exceptions\TravelRequest;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class TravelRequestException extends Exception
{
    public const NOT_FOUND       = 'travel_request.not_found';
    public const UNAUTHORIZED    = 'travel_request.unauthorized';
    public const NOT_EDITABLE    = 'travel_request.not_editable';
    public const NOT_CANCELLABLE = 'travel_request.not_cancellable';
    public const ALREADY_CANCELLED = 'travel_request.already_cancelled';
    public const ALREADY_EXPIRED   = 'travel_request.already_expired';
    public const CANNOT_CHANGE_OWN_STATUS = 'travel_request.cannot_change_own_status';

    public function getStatusCode(): int
    {
        return $this->code ?: Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
