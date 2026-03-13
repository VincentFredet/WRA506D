<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TicketVoter extends Voter
{
    public const string CREATE = 'TICKET_CREATE';
    public const string EDIT = 'TICKET_EDIT';
    public const string DELETE = 'TICKET_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::DELETE])
            && $subject instanceof Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return match ($attribute) {
            self::CREATE => true,
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => false,
            default => false,
        };
    }

    private function canEdit(Ticket $ticket, User $user): bool
    {
        if (in_array('ROLE_AGENT', $user->getRoles()) && $ticket->agent?->uuid?->equals($user->uuid)) {
            return true;
        }

        return $ticket->client?->uuid?->equals($user->uuid) ?? false;
    }
}
