<?php

namespace App\Service;

use App\DTO\MessageInterface;
use App\DTO\OutputMessage;
use App\Entity\UserBalance;
use App\Type\AmqpExchange;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ProduceMessageService
{
    private const MIN_VALUE_CHANGE = -100;
    private const MAX_VALUE_CHANGE = 100;

    public function __construct(
        private readonly RabbitMqBus $rabbitMqBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function produceMessages(int $startUserId, int $usersCount, int $messagesCount, int $messageVersion, AmqpExchange $exchange): void
    {
        $userBalanceRepository = $this->entityManager->getRepository(UserBalance::class);
        $currentUserId = $startUserId;
        $lastUserId = $currentUserId + $usersCount;
        $currentTime = new DateTime();
        while ($currentUserId < $lastUserId) {
            $userBalance = $userBalanceRepository->findOneBy(['userId' => $currentUserId]);
            $currentValue = $userBalance?->getValue() ?? 0;
            $currentMessageIdx = 0;
            while ($currentMessageIdx < $messagesCount) {
                $valueChange = random_int(self::MIN_VALUE_CHANGE, self::MAX_VALUE_CHANGE);
                $currentValue += $valueChange;
                $message = new OutputMessage(
                    $currentUserId,
                    $valueChange,
                    $currentValue,
                    $currentTime->add(DateInterval::createFromDateString('1 sec')),
                    $messageVersion,
                );
                $this->rabbitMqBus->publishToExchange($exchange, $message, (string)$currentUserId);
                $currentMessageIdx++;
            }
            $currentUserId++;
        }
    }
}
