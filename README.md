# Репозиторий для [публичного урока](https://otus.ru/lessons/symfony/#event-6064) по курсу [Symfony Framework](https://otus.ru/lessons/symfony/)

## Инициализация проекта

1. Запустить контейнеры командой `docker-compose up -d`
2. Войти в контейнер командой `docker exec -it rabbit-mq sh`
3. Включить плагин командой `rabbitmq-plugins enable rabbitmq_consistent_hash_exchange`
4. Выйти из контейенера
5. Войти в контейнер командой `docker exec -it php sh`
6. Создать директорию для логов командой `mkdir var/log`
7. Установить необходимые пакеты командой `composer install`
8. Инициализировать БД командой `php bin/console doctrine:migrations:migrate`
9. Выйти из контейнера
10. Перезапустить контейнер с консьюмерами командой `docker-compose restart supervisor`

## Запуск эксперимента с параллельными консьюмерами

1. Входим в контейнер командой `docker exec -it php sh`
2. Выполняем команду `php bin/console test:multi-consume 1`
3. В браузере заходим в Grafana по адресу http://localhost:3000 с реквизитами `admin` / `admin`
4. Добавляем новый источник данных `graphite:80`
5. Создаём дашборд и панель с визуализацией счётчиков `stats_counts.my_app.value_error` и
   `stats_counts.my_app.order_error`, видим ошибки обоих типов

## Запуск эксперимента с согласованным хэшированием

1. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 100`
2. В Grafana добавляем визуализацию счётчика `stats_counts.my_app.consumer_error`, видим, что при использовании
согласованного хэширования ошибок нет

## Добавляем консьюмеры в процессе работы

1. Выходим из контейнера, выполняем команду `docker-compose stop supervisor`
2. Входим обратно в контейнер командой `docker exec -it php sh`
3. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 200`
4. В файле `config/services.yaml` добавляем ещё 4 сервиса с консьюмерами
    ```yaml
    App\Controller\Amqp\ConsistentHash\Consumer4:
        class: App\Controller\Amqp\ConsistentHash\Consumer
        arguments:
            $consumerId: 4

    App\Controller\Amqp\ConsistentHash\Consumer5:
        class: App\Controller\Amqp\ConsistentHash\Consumer
        arguments:
            $consumerId: 5

    App\Controller\Amqp\ConsistentHash\Consumer6:
        class: App\Controller\Amqp\ConsistentHash\Consumer
        arguments:
            $consumerId: 6

    App\Controller\Amqp\ConsistentHash\Consumer7:
        class: App\Controller\Amqp\ConsistentHash\Consumer
        arguments:
            $consumerId: 7
    ```
5. В файле `config/packages/old_sound_rabbit_mq.yaml` добавляем ещё 4 консьюмера
    ```yaml
        consistent_hash4:
            <<: *consistentHash
            queue_options: { name: 'old_sound_rabbit_mq.consumer.consistent_hash4', routing_key: '32' }
            callback: App\Controller\Amqp\ConsistentHash\Consumer4
        consistent_hash5:
            <<: *consistentHash
            queue_options: { name: 'old_sound_rabbit_mq.consumer.consistent_hash5', routing_key: '32' }
            callback: App\Controller\Amqp\ConsistentHash\Consumer5
        consistent_hash6:
            <<: *consistentHash
            queue_options: { name: 'old_sound_rabbit_mq.consumer.consistent_hash6', routing_key: '32' }
            callback: App\Controller\Amqp\ConsistentHash\Consumer6
        consistent_hash7:
            <<: *consistentHash
            queue_options: { name: 'old_sound_rabbit_mq.consumer.consistent_hash7', routing_key: '32' }
            callback: App\Controller\Amqp\ConsistentHash\Consumer7

    ```
6. В файле `supervisor/consumer.conf` добавляем 4 новых процесса
    ```ini
    [program:consistent_hash4]
    command=php /app/bin/console rabbitmq:consumer consistent_hash4 -m 1000
    process_name=consistent_hash4_%(process_num)02d
    numprocs=1
    directory=/tmp
    autostart=true
    autorestart=true
    startsecs=3
    startretries=10
    user=www-data
    redirect_stderr=false
    stdout_logfile=/app/var/log/supervisor.consistent_hash4.out.log
    stdout_capture_maxbytes=1MB
    stderr_logfile=/app/var/log/supervisor.consistent_hash4.error.log
    stderr_capture_maxbytes=1MB
    
    [program:consistent_hash5]
    command=php /app/bin/console rabbitmq:consumer consistent_hash5 -m 1000
    process_name=consistent_hash3_%(process_num)02d
    numprocs=1
    directory=/tmp
    autostart=true
    autorestart=true
    startsecs=3
    startretries=10
    user=www-data
    redirect_stderr=false
    stdout_logfile=/app/var/log/supervisor.consistent_hash5.out.log
    stdout_capture_maxbytes=1MB
    stderr_logfile=/app/var/log/supervisor.consistent_hash5.error.log
    stderr_capture_maxbytes=1MB
    
    [program:consistent_hash6]
    command=php /app/bin/console rabbitmq:consumer consistent_hash6 -m 1000
    process_name=consistent_hash6_%(process_num)02d
    numprocs=1
    directory=/tmp
    autostart=true
    autorestart=true
    startsecs=3
    startretries=10
    user=www-data
    redirect_stderr=false
    stdout_logfile=/app/var/log/supervisor.consistent_hash6.out.log
    stdout_capture_maxbytes=1MB
    stderr_logfile=/app/var/log/supervisor.consistent_hash6.error.log
    stderr_capture_maxbytes=1MB
    
    [program:consistent_hash7]
    command=php /app/bin/console rabbitmq:consumer consistent_hash7 -m 1000
    process_name=consistent_hash7_%(process_num)02d
    numprocs=1
    directory=/tmp
    autostart=true
    autorestart=true
    startsecs=3
    startretries=10
    user=www-data
    redirect_stderr=false
    stdout_logfile=/app/var/log/supervisor.consistent_hash7.out.log
    stdout_capture_maxbytes=1MB
    stderr_logfile=/app/var/log/supervisor.consistent_hash7.error.log
    stderr_capture_maxbytes=1MB
    ```
7. Выходим из контейнера, выполняем команду `docker-compose start supervisor`
8. Входим обратно в контейнер командой `docker exec -it php sh`
9. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 200`
10. На графике в Grafana видим ошибки всех трёх типов.

## Переключаем консьюмеры при пустых очередях

1. Выходим из контейнера, выполняем команду `docker-compose stop supervisor`
2. Заходим в интерфейс RabbitMQ по адресу http://localhost:15672
3. Удаляем очереди `old_sound_rabbit_mq.consumer.consistent_hash` с номерами с 4 по 7 включительно
4. В файле `supervisor/consumer.conf` убираем 4 добавленных процесса
5. Входим в контейнер командой `docker exec -it php sh`
6. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 300`
7. Выходим из контейнера, выполняем команду `docker-compose start supervisor`, дожидаемся завершения обработки,
   выполняем команду `docker-compose stop supervisor`
8. В файле `supervisor/consumer.conf` возвращаем 4 удаленных процесса
9. Выполняем команду `docker-compose start supervisor`
10. Входим обратно в контейнер командой `docker exec -it php sh`
11. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 300`
12. На графике в Grafana видим ошибки только типа `consumer_error`

## Убираем коллизию, если сообщения попали на "новый" консьюмер

1. Исправляем класс `App\Service\ConsumeMessageService`
    ```php
    <?php
    
    namespace App\Service;
    
    use App\DTO\MessageInterface;
    use App\Entity\UserBalance;
    use Doctrine\ORM\EntityManagerInterface;
    
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
    ```
2. Выходим из контейнера, выполняем команду `docker-compose stop supervisor`
3. Заходим в интерфейс RabbitMQ по адресу http://localhost:15672
4. Удаляем очереди `old_sound_rabbit_mq.consumer.consistent_hash` с номерами с 4 по 7 включительно
5. В файле `supervisor/consumer.conf` убираем 4 добавленных процесса
6. Входим в контейнер командой `docker exec -it php sh`
7. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 400`
8. Выходим из контейнера, выполняем команду `docker-compose start supervisor`, дожидаемся завершения обработки,
   выполняем команду `docker-compose stop supervisor`
9. В файле `supervisor/consumer.conf` возвращаем 4 удаленных процесса
10. Выполняем команду `docker-compose start supervisor`
11. Входим обратно в контейнер командой `docker exec -it php sh`
12. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 400`
13. На графике в Grafana видим, что ошибок нет

## Добавляем версионирование

1. Добавляем класс `App\Entity\MessageVersion`
    ```php
    <?php
    
    namespace App\Entity;
    
    use DateTime;
    use Doctrine\ORM\Mapping as ORM;
    
    #[ORM\Entity]
    class MessageVersion
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer')]
        private ?int $id;
    
        public function __construct(
            #[ORM\Column(type: 'integer', unique: true)]
            private readonly int $consumerId,
            #[ORM\Column(type: 'integer')]
            private int $version,
        ) {
        }
    
        public function getId(): ?int
        {
            return $this->id;
        }
    
        public function getConsumerId(): int
        {
            return $this->consumerId;
        }
    
        public function getVersion(): int
        {
            return $this->version;
        }
    
        public function setId(?int $id): void
        {
            $this->id = $id;
        }
    
        public function setVersion(int $version): void
        {
            $this->version = $version;
        }
    }
    ```
2. В контейнере выполняем команды
    ```shell
    php bin/console doctrine:migrations:diff
    php bin/console doctrine:migrations:migrate
    ```
3. Исправляем класс `App\Service\ConsumeMessageService`
    ```php
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
    ```
4. В классе `App\Controller\Amqp\ConsistentHash\Consumer` исправляем метод `handle`
    ```php
    protected function handle($message): int
    {
        try {
            $this->messageService->processMessage($message, $this->consumerId);
        } catch (RuntimeException) {
            return self::MSG_REJECT_REQUEUE;
        }

        return self::MSG_ACK;
    }
    ```
5. Выходим из контейнера, выполняем команду `docker-compose stop supervisor`
6. Заходим в интерфейс RabbitMQ по адресу http://localhost:15672
7. Входим в контейнер командой `docker exec -it php sh`
8. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 500`
9. Выходим из контейнера, выполняем команду `docker-compose start supervisor`
10. Входим обратно в контейнер командой `docker exec -it php sh`
11. В контейнере выполняем команду `php bin/console test:consistent-hash-consume 500 --messageVerison=2`
12. На графике в Grafana видим только ошибки типа `value_error`

Автор: [Михаил Каморин](mailto:m.v.kamorin@gmail.com)
