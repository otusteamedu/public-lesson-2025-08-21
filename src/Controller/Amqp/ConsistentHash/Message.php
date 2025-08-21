<?php

namespace App\Controller\Amqp\ConsistentHash;

use App\DTO\MessageInterface;
use DateTime;

final readonly class Message implements MessageInterface
{
    public function __construct(
        private int $userId,
        private int $valueChange,
        private int $expectedValue,
        private DateTime $processedAt,
        private int $version,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getValueChange(): int
    {
        return $this->valueChange;
    }

    public function getExpectedValue(): int
    {
        return $this->expectedValue;
    }

    public function getProcessedAt(): DateTime
    {
        return $this->processedAt;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
