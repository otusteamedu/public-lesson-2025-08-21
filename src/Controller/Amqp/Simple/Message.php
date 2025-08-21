<?php

namespace App\Controller\Amqp\Simple;

use App\DTO\MessageInterface;
use DateTime;

final readonly class Message implements MessageInterface
{
    private const MESSAGE_VERSION = 1;

    public function __construct(
        private int $userId,
        private int $valueChange,
        private int $expectedValue,
        private DateTime $processedAt,
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
        return self::MESSAGE_VERSION;
    }
}
