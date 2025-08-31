<?php

namespace App\Domain\Shared\Entity;

class Entity implements EntityInterface
{
    public function isSoftDeleted(): bool
    {
        return method_exists($this, 'isDeleted') && $this->isDeleted();
    }
}
