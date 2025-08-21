<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserBalance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    public function __construct(
        #[ORM\Column(type: 'integer', unique: true)]
        private readonly int $userId,
        #[ORM\Column(type: 'integer')]
        private int $value,
        #[ORM\Column(type: 'integer')]
        private readonly int $consumerId,
        #[ORM\Column(type: 'datetime')]
        private DateTime $processedAt,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getConsumerId(): int
    {
        return $this->consumerId;
    }

    public function getProcessedAt(): DateTime
    {
        return $this->processedAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function updateValue(int $valueChange): void
    {
        $this->value += $valueChange;
    }

    public function setProcessedAt(DateTime $processedAt): void
    {
        $this->processedAt = $processedAt;
    }
}
