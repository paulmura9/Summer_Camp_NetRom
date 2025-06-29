<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $musical_genre = null;

    /**
     * @var Collection<int, FestivalArtist>
     */
    #[ORM\OneToMany(targetEntity: FestivalArtist::class, mappedBy: 'Artist')]
    private Collection $festivalArtists;

    public function __construct()
    {
        $this->festivalArtists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMusicalGenre(): ?string
    {
        return $this->musical_genre;
    }

    public function setMusicalGenre(string $musical_genre): static
    {
        $this->musical_genre = $musical_genre;

        return $this;
    }

    /**
     * @return Collection<int, FestivalArtist>
     */
    public function getFestivalArtists(): Collection
    {
        return $this->festivalArtists;
    }

    public function addFestivalArtist(FestivalArtist $festivalArtist): static
    {
        if (!$this->festivalArtists->contains($festivalArtist)) {
            $this->festivalArtists->add($festivalArtist);
            $festivalArtist->setArtist($this);
        }

        return $this;
    }

    public function removeFestivalArtist(FestivalArtist $festivalArtist): static
    {
        if ($this->festivalArtists->removeElement($festivalArtist)) {
            if ($festivalArtist->getArtist() === $this) {
                $festivalArtist->setArtist(null);
            }
        }

        return $this;
    }
}
