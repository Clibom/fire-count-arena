<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity;

use App\Infrastructure\Entity\Trait\HasUuidTrait;
use App\Infrastructure\Repository\DoctrineFireCountRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Doctrine entity for fire count persistence.
 * This is an infrastructure concern, separate from the domain model.
 */
#[ORM\Entity(repositoryClass: DoctrineFireCountRepository::class)]
#[ORM\Table(name: 'fire_count')]
class FireCountEntity
{
    use HasUuidTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'integer')]
    private int $adultsCount;

    #[ORM\Column(type: 'integer')]
    private int $childrenCount;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        Uuid $id,
        string $email,
        int $adultsCount,
        int $childrenCount,
        DateTimeImmutable $createdAt,
    ) {
        $this->setId($id);
        $this->email = $email;
        $this->adultsCount = $adultsCount;
        $this->childrenCount = $childrenCount;
        $this->createdAt = $createdAt;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAdultsCount(): int
    {
        return $this->adultsCount;
    }

    public function getChildrenCount(): int
    {
        return $this->childrenCount;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
