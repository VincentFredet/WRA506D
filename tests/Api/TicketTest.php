<?php declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Category;
use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\TicketPriority;
use App\Security\Tokens;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TicketTest extends ApiTestCase
{
    private EntityManagerInterface $em;
    private Tokens $tokens;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->tokens = static::getContainer()->get(Tokens::class);

        $connection = $this->em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['comment', 'ticket', 'category', 'user'] as $table) {
            $connection->executeStatement("DELETE FROM $table");
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function createUser(string $email, string $password = 'TestPassword123!', array $roles = []): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->email = $email;
        $user->roles = $roles;
        $user->password = $hasher->hashPassword($user, $password);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function createCategory(string $name = 'Technique'): Category
    {
        $category = new Category();
        $category->name = $name;

        $this->em->persist($category);
        $this->em->flush();

        return $category;
    }

    private function createTicket(User $client, Category $category, string $title = 'Ticket test'): Ticket
    {
        $ticket = new Ticket();
        $ticket->title = $title;
        $ticket->description = 'Description du ticket';
        $ticket->client = $client;
        $ticket->category = $category;

        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }

    public function testClientCanCreateTicket(): void
    {
        $user = $this->createUser('create@test.fr');
        $category = $this->createCategory();
        $token = $this->tokens->generateTokenForUser($user->getUserIdentifier());

        static::createClient()->request('POST', '/api/tickets', [
            'json' => [
                'title' => 'Mon problème',
                'description' => 'Description détaillée du problème',
                'priority' => TicketPriority::NORMAL->value,
                'category' => '/api/categories/' . $category->uuid,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Authorization' => $token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['title' => 'Mon problème']);
    }

    public function testTicketClientIsAutoSetFromAuth(): void
    {
        $user = $this->createUser('autoset@test.fr');
        $category = $this->createCategory('Auto');
        $token = $this->tokens->generateTokenForUser($user->getUserIdentifier());

        static::createClient()->request('POST', '/api/tickets', [
            'json' => [
                'title' => 'Test auto-set client',
                'description' => 'Le client doit être automatiquement assigné',
                'category' => '/api/categories/' . $category->uuid,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Authorization' => $token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['client' => '/api/users/' . $user->uuid]);
    }

    public function testUnauthenticatedCannotCreateTicket(): void
    {
        static::createClient()->request('POST', '/api/tickets', [
            'json' => [
                'title' => 'Sans auth',
                'description' => 'Ça ne devrait pas passer',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testClientCanEditOwnTicket(): void
    {
        $user = $this->createUser('edit@test.fr');
        $category = $this->createCategory('Edit');
        $ticket = $this->createTicket($user, $category);
        $token = $this->tokens->generateTokenForUser($user->getUserIdentifier());

        static::createClient()->request('PATCH', '/api/tickets/' . $ticket->uuid, [
            'json' => ['title' => 'Ticket modifié'],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Authorization' => $token,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['title' => 'Ticket modifié']);
    }

    public function testClientCannotEditOtherTicket(): void
    {
        $owner = $this->createUser('owner@test.fr');
        $other = $this->createUser('other@test.fr');
        $category = $this->createCategory('Other');
        $ticket = $this->createTicket($owner, $category);
        $token = $this->tokens->generateTokenForUser($other->getUserIdentifier());

        static::createClient()->request('PATCH', '/api/tickets/' . $ticket->uuid, [
            'json' => ['title' => 'Hijack'],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Authorization' => $token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testClientCannotDeleteTicket(): void
    {
        $user = $this->createUser('nodelete@test.fr');
        $category = $this->createCategory('NoDelete');
        $ticket = $this->createTicket($user, $category);
        $token = $this->tokens->generateTokenForUser($user->getUserIdentifier());

        static::createClient()->request('DELETE', '/api/tickets/' . $ticket->uuid, [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanDeleteTicket(): void
    {
        $client = $this->createUser('delclient@test.fr');
        $admin = $this->createUser('deladmin@test.fr', 'TestPassword123!', ['ROLE_ADMIN']);
        $category = $this->createCategory('Del');
        $ticket = $this->createTicket($client, $category);
        $token = $this->tokens->generateTokenForUser($admin->getUserIdentifier());

        static::createClient()->request('DELETE', '/api/tickets/' . $ticket->uuid, [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testClientSeesOnlyOwnTickets(): void
    {
        $client1 = $this->createUser('vis1@test.fr');
        $client2 = $this->createUser('vis2@test.fr');
        $category = $this->createCategory('Vis');

        $this->createTicket($client1, $category, 'Ticket client 1');
        $this->createTicket($client2, $category, 'Ticket client 2');

        $token = $this->tokens->generateTokenForUser($client1->getUserIdentifier());

        static::createClient()->request('GET', '/api/tickets', [
            'headers' => ['Authorization' => $token],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['totalItems' => 1]);
    }

    public function testRegisterWithShortPassword(): void
    {
        static::createClient()->request('POST', '/api/register', [
            'json' => [
                'email' => 'short@test.fr',
                'password' => 'court',
            ],
            'headers' => ['Content-Type' => 'application/ld+json'],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
