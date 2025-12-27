<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Trait for managing UUID primary keys on entities.
 * Provides a standardized way to handle UUIDs across all entities.
 */
trait HasUuidTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }
}
