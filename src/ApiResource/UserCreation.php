<?php declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\Post;
use App\State\UserCreationProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[Post(
    uriTemplate: '/register',
    processor: UserCreationProcessor::class,
    description: 'Créer un nouveau compte utilisateur',
)]
final class UserCreation
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, minMessage: 'Le mot de passe doit contenir au moins 10 caractères.')]
    public string $password;
}
