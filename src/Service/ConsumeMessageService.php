<?php

namespace App\Service;

use App\DTO\MessageInterface;
use App\Entity\MessageVersion;
use App\Entity\UserBalance;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class ConsumeMessageService
{
    private const MIN_PROCESS_TIME = 200_000;
    private const MAX_PROCESS_TIME = 1_500_000;
    private const NEW_CONSUMERS_OFFSET = 4;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MetricsService $metricsService,
    ) {
    }

    public function processMessage(MessageInterface $message, int $consumerId): void
    {
        usleep(random_int(self::MIN_PROCESS_TIME, self::MAX_PROCESS_TIME));
        $messageVersionRepository = $this->entityManager->getRepository(MessageVersion::class);
        /** @var MessageVersion|null $messageVersion */
        $messageVersion = $messageVersionRepository->findOneBy(['consumerId' => $consumerId]);
        if ($messageVersion === null) {
            $criteria = (new Criteria())->andWhere(Criteria::expr()->lt('version', $message->getVersion()));
            $oldMessageVersions = $messageVersionRepository->matching($criteria);
            if (!$oldMessageVersions->isEmpty()) {
                throw new RuntimeException('Should wait for old message versions processed');
            }
            $messageVersion = new MessageVersion($consumerId, $message->getVersion());
            $this->entityManager->persist($messageVersion);
            $this->entityManager->flush();
        } else {
            $messageVersion->setVersion($message->getVersion());
            $this->entityManager->flush();
        }
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
            if ($consumerId < self::NEW_CONSUMERS_OFFSET && $userBalance->getConsumerId() !== $consumerId) {
                $this->metricsService->incConsumerError();
            }
            $this->entityManager->flush();
            $this->metricsService->incProcessedVersion($message->getVersion());
        }
    }
}
