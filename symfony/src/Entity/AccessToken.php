<?php

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
class AccessToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 256)]
    private ?string $value = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $lastUpdate = null;

    #[ORM\OneToOne(mappedBy: 'token', cascade: ['persist'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTimeInterface $lastUpdate): static
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setToken(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getToken() !== $this) {
            $user->setToken($this);
        }

        $this->user = $user;

        return $this;
    }

    public function isValid(): bool
    {
        /** @var DateTimeInterface */
        $date = $this->getLastUpdate();
        if (intval($date->sub(new DateInterval('PT60M'))->diff(new DateTime('now', new DateTimeZone('Europe/Paris')))->format('%i')) >= 15)
        {
            return false;
        } else {
            return true;
        }
    }
}
