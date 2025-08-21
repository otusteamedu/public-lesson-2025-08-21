<?php

namespace App\Command;

use App\Service\ProduceMessageService;
use App\Type\AmqpExchange;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'test:consistent-hash-consume')]
class ConsistentHashConsumerTestCommand extends BaseCommand
{
    private const DEFAULT_MESSAGES_PER_USER_COUNT = 10;
    private const DEFAULT_USERS_COUNT = 20;
    private const DEFAULT_MESSAGE_VERSION = 1;

    public function __construct(
        private readonly ProduceMessageService $produceMessageService,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('offset', InputArgument::REQUIRED, 'First user id')
            ->addOption('messages', 'm', InputOption::VALUE_REQUIRED, 'Messages count per user')
            ->addOption('users', 'u', InputOption::VALUE_REQUIRED, 'Users count')
            ->addOption('messageVersion', null, InputOption::VALUE_REQUIRED, 'Messages version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $messages = $input->getOption('messages') ?? self::DEFAULT_MESSAGES_PER_USER_COUNT;
        $users = $input->getOption('users') ?? self::DEFAULT_USERS_COUNT;
        $version = $input->getOption('messageVersion') ?? self::DEFAULT_MESSAGE_VERSION;
        $startUserId = $input->getArgument('offset');
        $this->produceMessageService->produceMessages(
            $startUserId,
            $users,
            $messages,
            $version,
            AmqpExchange::ConsistentHash
        );

        return self::SUCCESS;
    }
}
