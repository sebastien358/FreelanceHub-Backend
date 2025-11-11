<?php

namespace App\Entity;

use App\Repository\TestamonialRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TestamonialRepository::class)]
class Testamonial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['testimonial'])]
    private ?int $id = null;

    #[ORM\Column(length: 125)]
    #[Groups(['testimonial'])]
    private ?string $name = null;

    #[ORM\Column(length: 125)]
    #[Groups(['testimonial'])]
    private ?string $job = null;

    #[ORM\Column(length: 255)]
    #[Groups(['testimonial'])]
    private ?string $message = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['testimonial'])]
    private ?bool $isRead = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['testimonial'])]
    private ?bool $isPublished = false;

    #[ORM\OneToOne(targetEntity: Picture::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['testimonial'])]
    private ?Picture $picture = null;

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

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(string $job): static
    {
        $this->job = $job;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    public function setPicture(?Picture $picture): static
    {
        $this->picture = $picture;

        return $this;
    }
}
