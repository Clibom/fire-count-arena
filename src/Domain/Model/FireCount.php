<?php

declare(strict_types=1);

namespace App\Domain\Model;

use DateTimeImmutable;

/**
 * Domain model representing a fire count submission.
 * This is a pure domain object with no infrastructure dependencies.
 */
final readonly class FireCount
{
    public function __construct(
        public string $id,
        public string $email,
        public int $adultsCount,
        public int $childrenCount,
        public DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * Returns the total number of people.
     */
    public function getTotalPeople(): int
    {
        return $this->adultsCount + $this->childrenCount;
    }
}
