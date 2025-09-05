<?php

declare(strict_types=1);

namespace App\Domain\Country\Entity;

use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Domain\Region\Entity\Region;
use App\Domain\Shared\Entity\Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[Gedmo\SoftDeleteable]
class Country extends Entity
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid', unique: true, nullable: false)]
    private UuidInterface $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $iso;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(length: 255, nullable: false)]
    private string $capital;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $population;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $phoneCode;

    #[ORM\ManyToOne(targetEntity: MacroRegion::class, inversedBy: 'countries')]
    #[ORM\JoinColumn(name: 'macro_region_id', referencedColumnName: 'id', nullable: false)]
    private MacroRegion $macroRegion;

    #[ORM\OneToMany(targetEntity: Region::class, mappedBy: 'country')]
    private Collection $regions;

    #[ORM\Column(nullable: true)]
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

    public function getIso(): string
    {
        return $this->iso;
    }

    public function setIso(string $iso): static
    {
        $this->iso = $iso;

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

    public function getCapital(): string
    {
        return $this->capital;
    }

    public function setCapital(string $capital): static
    {
        $this->capital = $capital;

        return $this;
    }

    public function getPopulation(): int
    {
        return $this->population;
    }

    public function setPopulation(int $population): static
    {
        $this->population = $population;

        return $this;
    }

    public function getPhoneCode(): int
    {
        return $this->phoneCode;
    }

    public function setPhoneCode(int $phoneCode): static
    {
        $this->phoneCode = $phoneCode;

        return $this;
    }

    public function getMacroRegion(): MacroRegion
    {
        return $this->macroRegion;
    }

    public function setMacroRegion(MacroRegion $macroRegion): static
    {
        $this->macroRegion = $macroRegion;

        return $this;
    }

    public function getRegions(): Collection
    {
        return $this->regions;
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
        string $iso,
        string $name,
        string $capital,
        int $population,
        int $phoneCode,
        MacroRegion $macroRegion,
    ): self {
        $self = new self();

        $self->setId($id);
        $self->setIso($iso);
        $self->setName($name);
        $self->setCapital($capital);
        $self->setPopulation($population);
        $self->setPhoneCode($phoneCode);
        $self->setMacroRegion($macroRegion);

        return $self;
    }
}
