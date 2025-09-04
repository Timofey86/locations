<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Entity;

use App\Domain\Country\Entity\Country;
use App\Domain\MacroRegion\Infrastrucrure\Doctrine\Repository\MacroRegionRepository;
use App\Domain\Shared\Entity\Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: MacroRegionRepository::class)]
#[Gedmo\SoftDeleteable]
class MacroRegion extends Entity
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid', unique: true, nullable: false)]
    private UuidInterface $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $code;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $name;

    #[ORM\OneToMany(targetEntity: Country::class, mappedBy: 'macroRegion')]
    private Collection $countries;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $sorting = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $geonameId = null;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCountries(): Collection
    {
        return $this->countries;
    }


    public function getSorting(): ?int
    {
        return $this->sorting;
    }

    public function setSorting(?int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    public function getGeonameId(): ?int
    {
        return $this->geonameId;
    }

    public function setGeonameId(?int $geonameId): static
    {
        $this->geonameId = $geonameId;

        return $this;
    }

    public static function create(
        UuidInterface $id,
        string $code,
        string $name,
    ): self {
        $self = new self();

        $self->setId($id);
        $self->setCode($code);
        $self->setName($name);

        return $self;
    }
}
