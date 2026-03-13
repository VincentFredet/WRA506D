<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Doctrine\Traits\CreatedAtTrait;
use App\Doctrine\Traits\UpdatedAtTrait;
use App\Doctrine\Traits\UuidTrait;
use App\Enum\TableEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: TableEnum::TABLE_USER)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    description: 'Utilisateurs de la plateforme de ticketing',
)]
#[Get(security: 'is_granted("ROLE_USER")')]
#[GetCollection(security: 'is_granted("ROLE_USER")')]
#[Patch(security: 'is_granted("ROLE_ADMIN")')]
#[Delete(security: 'is_granted("ROLE_ADMIN")')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UuidTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[ApiProperty(writable: false, readable: true)]
    public ?string $email = null;

    #[ORM\Column]
    public array $roles = [];

    #[ORM\Column]
    #[Ignore]
    public ?string $password = null;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'client')]
    #[Ignore]
    public Collection $tickets;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'agent')]
    #[Ignore]
    public Collection $assignedTickets;

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author')]
    #[Ignore]
    public Collection $comments;

    public function __construct()
    {
        $this->defineUuid();
        $this->setCreatedAt();
        $this->tickets = new ArrayCollection();
        $this->assignedTickets = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
    }
}
