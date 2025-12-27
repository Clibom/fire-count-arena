<?php

declare(strict_types=1);

namespace App\UseCases\ViewFireCount;

/**
 * Query DTO for retrieving a fire count by ID.
 */
final readonly class ViewFireCountQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
