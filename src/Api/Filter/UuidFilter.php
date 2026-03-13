<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Uid\Uuid;
use Throwable;
use function array_filter;
use function array_map;
use function is_array;
use function sprintf;
use function str_ends_with;
use function Symfony\Component\String\u;

final class UuidFilter extends AbstractFilter
{
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];

        foreach ($this->properties as $property => $strategy) {
            if (
                !$this->isPropertyEnabled($property, $resourceClass)
                || !$this->isPropertyMapped($property, $resourceClass, true)
            ) {
                continue;
            }

            /** @var string[] $filterParameterNames */
            $filterParameterNames = [$property, "{$property}[]"];

            foreach ($filterParameterNames as $filterParameterName) {
                $description[$filterParameterName] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'is_collection' => str_ends_with($filterParameterName, '[]'),
                ];
            }
        }

        return $description;
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
        if (
            null === $value
            || !$this->isPropertyEnabled($property, $resourceClass)
            || !$this->isPropertyMapped($property, $resourceClass, true)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $valueParameter = ':' . $queryNameGenerator->generateParameterName($property);
        $aliasedField = sprintf('%s.%s', $alias, $property);

        if (is_array($value)) {
            $values = array_filter(array_map(
                fn(string $value) => $this->convertUuidFromStringToBinary($value),
                $value,
            ));

            if (empty($values)) {
                return;
            }

            $queryBuilder
                ->andWhere($queryBuilder->expr()->in($aliasedField, $valueParameter))
                ->setParameter($valueParameter, $values);

            return;
        }

        if (null === $value = $this->convertUuidFromStringToBinary($value)) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq($aliasedField, $valueParameter))
            ->setParameter($valueParameter, $value);
    }

    private function convertUuidFromStringToBinary(string $value): ?string
    {
        if ('/' === ($value[0] ?? '')) {
            $value = (string) u($value)->afterLast('/');
        }

        try {
            return Uuid::fromRfc4122($value)->toBinary();
        } catch (Throwable) {
            return null;
        }
    }
}
