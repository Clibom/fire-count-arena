<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

/**
 * Exception thrown when a fire count cannot be found by ID.
 */
final class FireCountNotFoundException extends DomainException
{
    public static function byId(string $id): self
    {
        return new self(sprintf('Fire count with ID "%s" not found.', $id));
    }
}
