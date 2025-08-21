<?php

namespace App\DTO;

use DateTime;

interface MessageInterface
{
    public function getUserId(): int;

    public function getValueChange(): int;

    public function getExpectedValue(): int;

    public function getProcessedAt(): DateTime;

    public function getVersion(): int;
}
