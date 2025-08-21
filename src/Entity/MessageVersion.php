<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MessageVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    public function __construct(
        #[ORM\Column(type: 'integer', unique: true)]
        private readonly int $consumerId,
        #[ORM\Column(type: 'integer')]
        private int $version,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsumerId(): int
    {
        return $this->consumerId;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }
}
