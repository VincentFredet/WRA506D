<?php declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class MaxOpenTickets extends Constraint
{
    public string $message = 'Vous avez déjà 10 tickets ouverts. Impossible d\'en créer un nouveau.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
