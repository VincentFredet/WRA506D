<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::postPersist, entity: Ticket::class)]
final readonly class TicketCreatedListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function postPersist(Ticket $ticket): void
    {
        $this->logger->info('New ticket created', [
            'uuid' => (string) $ticket->uuid,
            'title' => $ticket->title,
            'client' => $ticket->client?->email,
            'priority' => $ticket->priority->value,
        ]);
    }
}
