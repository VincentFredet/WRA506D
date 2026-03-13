<?php declare(strict_types=1);

namespace App\Enum;

enum TicketStatus: string
{
    case OPEN = 'ouvert';
    case IN_PROGRESS = 'en_cours';
    case RESOLVED = 'résolu';
    case CLOSED = 'fermé';
}
