<?php declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\TicketStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class MaxOpenTicketsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof Ticket) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        $count = $this->em->getRepository(Ticket::class)->count([
            'client' => $user,
            'status' => TicketStatus::OPEN,
        ]);

        if ($count >= 10) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
