<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\OnlyWithTodoFilter;
use App\Doctrine\Traits\CreatedAtTrait;
use App\Doctrine\Traits\UpdatedAtTrait;
use App\Doctrine\Traits\UuidTrait;
use App\Enum\TableEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: TableEnum::TABLE_CATEGORY)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    description: 'Catégories de tickets (Facturation, Technique, Bug logiciel...)',
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => SearchFilterInterface::STRATEGY_PARTIAL])]
#[ApiFilter(OnlyWithTodoFilter::class)]
#[Get(security: 'is_granted("ROLE_USER")')]
#[GetCollection(security: 'is_granted("ROLE_USER")')]
#[Post(security: 'is_granted("ROLE_ADMIN")')]
#[Patch(security: 'is_granted("ROLE_ADMIN")')]
#[Delete(security: 'is_granted("ROLE_ADMIN")')]
class Category
{
    use UuidTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public ?string $name = null;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'category')]
    #[Ignore]
    public Collection $tickets;

    public function __construct()
    {
        $this->defineUuid();
        $this->setCreatedAt();
        $this->tickets = new ArrayCollection();
    }
}
