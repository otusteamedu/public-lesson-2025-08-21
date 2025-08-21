<?php

namespace App\Service;

use App\DTO\MessageInterface;
use App\Entity\UserBalance;
use Doctrine\ORM\EntityManagerInterface;

class ConsumeMessageService
{
    private const MIN_PROCESS_TIME = 200_000;
    private const MAX_PROCESS_TIME = 1_500_000;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MetricsService $metricsService,
    ) {
    }

    public function processMessage(MessageInterface $message, int $consumerId): void
    {
        usleep(random_int(self::MIN_PROCESS_TIME, self::MAX_PROCESS_TIME));
        $userBalanceRepository = $this->entityManager->getRepository(UserBalance::class);
        $userBalance = $userBalanceRepository->findOneBy(['userId' => $message->getUserId()]);
        if ($userBalance === null) {
            $userBalance = new UserBalance(
                $message->getUserId(),
                $message->getValueChange(),
                $consumerId,
                $message->getProcessedAt()
            );
            $this->entityManager->persist($userBalance);
            $this->entityManager->flush();
            $this->metricsService->incProcessedVersion($message->getVersion());
        } else {
            var_dump($message->getProcessedAt());
            var_dump($userBalance->getProcessedAt());
            if ($userBalance->getProcessedAt() >= $message->getProcessedAt()) {
                $this->metricsService->incOrderError();
            }
            $userBalance->setProcessedAt($message->getProcessedAt());
            $userBalance->updateValue($message->getValueChange());
            if ($userBalance->getValue() !== $message->getExpectedValue()) {
                $this->metricsService->incValueError();
            }
            if ($userBalance->getConsumerId() !== $consumerId) {
                $this->metricsService->incConsumerError();
            }
            $this->entityManager->flush();
            $this->metricsService->incProcessedVersion($message->getVersion());
        }
    }
}
