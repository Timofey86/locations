<?php

declare(strict_types=1);

namespace App\Domain\Shared\Trait;

use Doctrine\ORM\Mapping as ORM;

/**
 * add #[Gedmo\SoftDeleteable] to entity.
 */
trait SoftDeleteableTrait
{
    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function restoreEntity(): void
    {
        $this->deletedAt = null;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }
}
