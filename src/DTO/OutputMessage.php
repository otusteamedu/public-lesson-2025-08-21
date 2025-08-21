<?php

namespace App\DTO;

use DateTime;

final readonly class OutputMessage
{
    public function __construct(
        public int $userId,
        public int $valueChange,
        public int $expectedValue,
        public DateTime $processedAt,
        public int $version,
    ) {
    }
}
