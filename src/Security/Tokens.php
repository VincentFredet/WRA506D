<?php declare(strict_types=1);

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class Tokens
{
    public function __construct(
        #[Autowire(param: 'kernel.secret')]
        private string $secret,
    ) {
    }

    public function generateTokenForUser(string $email, \DateTime $expire = new \DateTime('+4 hours')): string
    {
        $encoded = json_encode([
            'email' => $email,
            'expire' => $expire->getTimestamp(),
        ]);

        return base64_encode(json_encode([
            $encoded,
            $this->sign($encoded),
        ]));
    }

    public function decodeUserToken(?string $token): ?string
    {
        try {
            [$info, $sign] = json_decode(base64_decode($token), true);

            if ($sign !== $this->sign($info)) {
                return null;
            }

            $info = json_decode($info, true);

            if ($info['expire'] < time()) {
                return null;
            }

            if (isset($info['email']) && filter_var($info['email'], FILTER_VALIDATE_EMAIL)) {
                return $info['email'];
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function sign(string $encoded): string
    {
        return hash('sha256', $encoded . '/' . $this->secret);
    }
}
