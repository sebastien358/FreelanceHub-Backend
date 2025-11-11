<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['contacts', 'contact'])]
    private ?int $id = null;

    #[ORM\Column(length: 125)]
    #[Groups(['contacts', 'contact'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['contacts', 'contact'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['contacts', 'contact'])]
    private ?string $message = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['contacts', 'contact'])]
    private ?bool $isRead = false;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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
}
