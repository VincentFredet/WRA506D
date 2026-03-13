<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Category;
use App\Enum\TicketStatus;
use Doctrine\ORM\QueryBuilder;

final class OnlyWithTodoFilter extends AbstractFilter
{
    public function getDescription(string $resourceClass): array
    {
        return [
            'onlyWithTodo' => [
                'property' => null,
                'type' => 'bool',
                'required' => false,
                'description' => 'Filtrer uniquement les catégories ayant des tickets ouverts ou en cours.',
                'openapi' => [
                    'allowEmptyValue' => false,
                ],
            ],
        ];
    }

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($property !== 'onlyWithTodo' || $resourceClass !== Category::class) {
            return;
        }

        if ($value !== 'true' && $value !== '1') {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $ticketAlias = $queryNameGenerator->generateJoinAlias('tickets');

        $queryBuilder
            ->innerJoin("{$alias}.tickets", $ticketAlias)
            ->andWhere("{$ticketAlias}.status IN (:todo_statuses)")
            ->setParameter('todo_statuses', [TicketStatus::OPEN->value, TicketStatus::IN_PROGRESS->value])
            ->groupBy("{$alias}.uuid");
    }
}
