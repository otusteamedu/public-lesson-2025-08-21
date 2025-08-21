<?php

namespace App\Controller\Amqp\Simple;

use App\Controller\Amqp\AbstractConsumer;
use App\Service\ConsumeMessageService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class Consumer extends AbstractConsumer
{
    private const CONSUMER_VERSION = 1;

    public function __construct(
        private readonly ConsumeMessageService $messageService,
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
            $this->messageService->processMessage($message, self::CONSUMER_VERSION);
        } catch (UniqueConstraintViolationException) {
            return self::MSG_REJECT_REQUEUE;
        }

        return self::MSG_ACK;
    }
}
