<?php declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserCreation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserCreationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $user = new User();
        $user->email = $data->email;
        $user->password = $this->hasher->hashPassword($user, $data->password);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
