<?php

namespace App\Controller\Amqp\ConsistentHash;

use App\Controller\Amqp\AbstractConsumer;
use App\Service\ConsumeMessageService;
use RuntimeException;

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
        try {
            $this->messageService->processMessage($message, $this->consumerId);
        } catch (RuntimeException) {
            return self::MSG_REJECT_REQUEUE;
        }

        return self::MSG_ACK;
    }
}
