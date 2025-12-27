<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\FireCount;

/**
 * Repository interface for FireCount persistence.
 * Defined in Domain layer to maintain dependency inversion.
 */
interface FireCountRepositoryInterface
{
    /**
     * Persists a new fire count submission.
     */
    public function save(FireCount $fireCount): void;

    /**
     * Finds a fire count by its UUID.
     * Returns null if not found.
     */
    public function findById(string $id): ?FireCount;
}
