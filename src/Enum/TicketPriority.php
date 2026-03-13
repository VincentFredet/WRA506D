<?php declare(strict_types=1);

namespace App\Enum;

enum TicketPriority: string
{
    case LOW = 'faible';
    case NORMAL = 'normale';
    case HIGH = 'haute';
}
