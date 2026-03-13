<?php declare(strict_types=1);

namespace App\Security\Authenticator;

use App\Entity\User;
use App\Security\Tokens;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class TokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly Tokens $tokens,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get('Authorization');

        return new SelfValidatingPassport(new UserBadge($token, function (string $token): ?User {
            if (null !== $email = $this->tokens->decodeUserToken($token)) {
                return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            }

            return null;
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => 'Authentication failure.'], Response::HTTP_UNAUTHORIZED);
    }
}
