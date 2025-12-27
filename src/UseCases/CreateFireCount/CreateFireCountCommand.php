<?php

declare(strict_types=1);

namespace App\UseCases\CreateFireCount;

/**
 * Command DTO for creating a fire count submission.
 * Immutable value object containing input data.
 */
final readonly class CreateFireCountCommand
{
    public function __construct(
        public string $email,
        public int $adultsCount,
        public int $childrenCount,
    ) {
    }
}
