<?php

declare(strict_types=1);

namespace App\UseCases\ViewFireCount;

use App\Domain\Exception\FireCountNotFoundException;
use App\Domain\Model\FireCount;
use App\Domain\Repository\FireCountRepositoryInterface;

/**
 * Handler for viewing a fire count summary.
 */
final readonly class ViewFireCountHandler
{
    public function __construct(
        private FireCountRepositoryInterface $repository,
    ) {
    }

    public function handle(ViewFireCountQuery $query): FireCount
    {
        $fireCount = $this->repository->findById($query->id);

        if ($fireCount === null) {
            throw FireCountNotFoundException::byId($query->id);
        }

        return $fireCount;
    }
}
