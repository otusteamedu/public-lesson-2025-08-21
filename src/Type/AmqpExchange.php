<?php

namespace App\Type;

enum AmqpExchange: string
{
    case Simple = 'simple';
    case ConsistentHash = 'consistent_hash';
}
