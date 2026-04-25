<?php

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Thrown by OrderStatusTransitionService when an actor (admin or delivery
 * person) attempts a status change that isn't allowed for them. Carries
 * a 422 so the bootstrap exception renderer maps it to a clean
 * `{ success: false, error: ... }` payload.
 */
class InvalidOrderTransitionException extends RuntimeException implements HttpExceptionInterface
{
    public function getStatusCode(): int
    {
        return 422;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
