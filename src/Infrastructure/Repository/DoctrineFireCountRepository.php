<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Model\FireCount;
use App\Domain\Repository\FireCountRepositoryInterface;
use App\Infrastructure\Entity\FireCountEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * Doctrine implementation of the FireCount repository.
 * Handles mapping between domain model and infrastructure entity.
 *
 * @extends ServiceEntityRepository<FireCountEntity>
 */
class DoctrineFireCountRepository extends ServiceEntityRepository implements FireCountRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FireCountEntity::class);
    }

    public function save(FireCount $fireCount): void
    {
        $entity = new FireCountEntity(
            Uuid::fromString($fireCount->id),
            $fireCount->email,
            $fireCount->adultsCount,
            $fireCount->childrenCount,
            $fireCount->createdAt,
        );

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?FireCount
    {
        $entity = $this->find(Uuid::fromString($id));

        if ($entity === null) {
            return null;
        }

        return $this->toDomainModel($entity);
    }

    private function toDomainModel(FireCountEntity $entity): FireCount
    {
        return new FireCount(
            $entity->getId()->toRfc4122(),
            $entity->getEmail(),
            $entity->getAdultsCount(),
            $entity->getChildrenCount(),
            $entity->getCreatedAt(),
        );
    }
}
