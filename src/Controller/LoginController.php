<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Security\Tokens;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user, Tokens $tokens): Response
    {
        if (null === $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->json([
            'token' => $tokens->generateTokenForUser($user->getUserIdentifier()),
            'user' => $user->getUserIdentifier(),
        ]);
    }
}
