<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Testamonial::class, inversedBy: "picture")]
    #[ORM\JoinColumn(nullable: true, onDelete: "cascade")]
    private ?Testamonial $testamonial = null;

    #[ORM\Column(length: 255)]
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
