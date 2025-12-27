<?php

declare(strict_types=1);

namespace App\UseCases\SendFireCountEmail;

/**
 * Command DTO for sending fire count confirmation email.
 */
final readonly class SendFireCountEmailCommand
{
    public function __construct(
        public string $email,
        public string $fireCountId,
        public int $adultsCount,
        public int $childrenCount,
    ) {
    }
}
