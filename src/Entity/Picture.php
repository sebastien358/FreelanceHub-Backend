<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['picture'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Testamonial::class, inversedBy: "picture")]
    #[ORM\JoinColumn(nullable: true, onDelete: "cascade")]
    #[Groups(['picture'])]
    private ?Testamonial $testamonial = null;

    #[ORM\Column(length: 255)]
    #[Groups(['picture'])]
    private ?string $filename = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getTestamonial(): ?Testamonial
    {
        return $this->testamonial;
    }

    public function setTestamonial(?Testamonial $testamonial): static
    {
        $this->testamonial = $testamonial;

        return $this;
    }
}
