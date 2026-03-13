<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Doctrine\Traits\CreatedAtTrait;
use App\Doctrine\Traits\UpdatedAtTrait;
use App\Doctrine\Traits\UuidTrait;
use App\Enum\TableEnum;
use App\State\CommentCreationProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: TableEnum::TABLE_COMMENT)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    description: 'Commentaires sur les tickets',
)]
#[Get(security: 'is_granted("ROLE_USER")')]
#[GetCollection(security: 'is_granted("ROLE_USER")')]
#[Post(
    security: 'is_granted("ROLE_USER")',
    processor: CommentCreationProcessor::class,
)]
#[Patch(security: 'is_granted("COMMENT_EDIT", object)')]
#[Delete(security: 'is_granted("ROLE_ADMIN")')]
class Comment
{
    use UuidTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'uuid')]
    public ?User $author = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'uuid')]
    #[Assert\NotNull]
    public ?Ticket $ticket = null;

    public function __construct()
    {
        $this->defineUuid();
        $this->setCreatedAt();
    }
}
