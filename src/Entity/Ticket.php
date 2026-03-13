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
use App\Api\Filter\UuidFilter;
use App\Doctrine\Traits\CreatedAtTrait;
use App\Doctrine\Traits\UpdatedAtTrait;
use App\Doctrine\Traits\UuidTrait;
use App\Enum\TableEnum;
use App\Enum\TicketPriority;
use App\Enum\TicketStatus;
use App\State\TicketCreationProcessor;
use App\Validator\Constraints\MaxOpenTickets;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: TableEnum::TABLE_TICKET)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    description: 'Tickets de support technique',
)]
#[ApiFilter(SearchFilter::class, properties: ['title' => SearchFilterInterface::STRATEGY_PARTIAL, 'status' => SearchFilterInterface::STRATEGY_EXACT, 'priority' => SearchFilterInterface::STRATEGY_EXACT])]
#[ApiFilter(UuidFilter::class, properties: ['category', 'client', 'agent'])]
#[MaxOpenTickets(groups: ['create'])]
#[Get(security: 'is_granted("ROLE_USER")')]
#[GetCollection(security: 'is_granted("ROLE_USER")')]
#[Post(
    processor: TicketCreationProcessor::class,
    securityPostDenormalize: 'is_granted("TICKET_CREATE", object)',
    validationContext: ['groups' => ['Default', 'create']],
)]
#[Patch(security: 'is_granted("TICKET_EDIT", object)')]
#[Delete(security: 'is_granted("TICKET_DELETE", object)')]
class Ticket
{
    use UuidTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $description = null;

    #[ORM\Column(length: 20, enumType: TicketPriority::class)]
    #[Assert\NotNull]
    public TicketPriority $priority = TicketPriority::NORMAL;

    #[ORM\Column(length: 20, enumType: TicketStatus::class)]
    #[Assert\NotNull]
    public TicketStatus $status = TicketStatus::OPEN;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'uuid')]
    public ?User $client = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedTickets')]
    #[ORM\JoinColumn(nullable: true, referencedColumnName: 'uuid')]
    public ?User $agent = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'uuid')]
    #[Assert\NotNull]
    public ?Category $category = null;

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'ticket', cascade: ['remove'])]
    public Collection $comments;

    public function __construct()
    {
        $this->defineUuid();
        $this->setCreatedAt();
        $this->comments = new ArrayCollection();
    }
}
