<?php

namespace App\Service;

use App\Type\ConsumeType;
use App\Type\ProduceType;
use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection\UdpSocket;

class MetricsService
{
    private const DEFAULT_SAMPLE_RATE = 1.0;
    private const VALUE_ERROR = 'value_error';
    private const ORDER_ERROR = 'order_error';
    private const CONSUMER_ERROR = 'consumer_error';
    private const VERSION_PROCESSED_TEMPLATE = 'processed_version.%d';

    private Client $client;

    public function __construct(string $host, int $port, string $namespace)
    {
        $connection = new UdpSocket($host, $port);
        $this->client = new Client($connection, $namespace);
    }

    public function incValueError(): void
    {
        $this->increment(self::VALUE_ERROR);
    }

    public function incOrderError(): void
    {
        $this->increment(self::ORDER_ERROR);
    }

    public function incProcessedVersion(int $version): void
    {
        $this->increment(sprintf(self::VERSION_PROCESSED_TEMPLATE, $version));
    }

    public function incConsumerError(): void
    {
        $this->increment(self::CONSUMER_ERROR);
    }

    private function increment(string $key, ?float $sampleRate = null, ?array $tags = null): void
    {
        $this->client->increment($key, $sampleRate ?? self::DEFAULT_SAMPLE_RATE, $tags ?? []);
    }
}
