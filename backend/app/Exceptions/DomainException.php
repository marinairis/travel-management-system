<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class DomainException extends Exception
{
    abstract public function getStatusCode(): int;

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __("messages.{$this->getMessage()}"),
        ], $this->getStatusCode());
    }
}
