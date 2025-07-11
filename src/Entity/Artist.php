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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;
    /**
     * @var Collection<int, FestivalArtist>
     */
    #[ORM\OneToMany(targetEntity: FestivalArtist::class, mappedBy: 'artist')]
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $spotifyProfile = null;

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;
        return $this;
    }

    public function getSpotifyProfile(): ?string
    {
        return $this->spotifyProfile;
    }

    public function setSpotifyProfile(?string $spotifyProfile): static
    {
        $this->spotifyProfile = $spotifyProfile;
        return $this;
    }

}
