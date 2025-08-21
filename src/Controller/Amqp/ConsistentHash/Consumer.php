<?php

namespace App\Controller\Amqp\ConsistentHash;

use App\Controller\Amqp\AbstractConsumer;
use App\Service\ConsumeMessageService;

class Consumer extends AbstractConsumer
{
    public function __construct(
        private readonly ConsumeMessageService $messageService,
        private readonly int $consumerId,
    ) {
    }
    protected function getMessageClass(): string
    {
        return Message::class;
    }

    /**
     * @param Message $message
     */
    protected function handle($message): int
    {
        $this->messageService->processMessage($message, $this->consumerId);

        return self::MSG_ACK;
    }
}
