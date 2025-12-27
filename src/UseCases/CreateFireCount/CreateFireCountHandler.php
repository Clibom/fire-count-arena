<?php

declare(strict_types=1);

namespace App\UseCases\CreateFireCount;

use App\Domain\Model\FireCount;
use App\Domain\Repository\FireCountRepositoryInterface;
use App\UseCases\SendFireCountEmail\SendFireCountEmailCommand;
use App\UseCases\SendFireCountEmail\SendFireCountEmailHandler;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Handler for creating a fire count submission.
 * Orchestrates domain model creation, persistence, and email notification.
 */
final readonly class CreateFireCountHandler
{
    public function __construct(
        private FireCountRepositoryInterface $repository,
        private SendFireCountEmailHandler $emailHandler,
    ) {
    }

    public function handle(CreateFireCountCommand $command): FireCount
    {
        $fireCount = new FireCount(
            id: Uuid::v4()->toRfc4122(),
            email: $command->email,
            adultsCount: $command->adultsCount,
            childrenCount: $command->childrenCount,
            createdAt: new DateTimeImmutable(),
        );

        $this->repository->save($fireCount);

        $this->emailHandler->handle(new SendFireCountEmailCommand(
            email: $fireCount->email,
            fireCountId: $fireCount->id,
            adultsCount: $fireCount->adultsCount,
            childrenCount: $fireCount->childrenCount,
        ));

        return $fireCount;
    }
}
