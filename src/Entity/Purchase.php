<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Festival;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(name: 'festival_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Festival $festival = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFestival(): ?festival
    {
        return $this->festival;
    }

    public function setFestival(?festival $festival): static
    {
        $this->festival = $festival;

        return $this;
    }
}
