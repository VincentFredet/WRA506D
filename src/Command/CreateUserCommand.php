<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un utilisateur en base de données',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addOption('role', 'r', InputOption::VALUE_REQUIRED, 'Rôle (ROLE_ADMIN, ROLE_AGENT)', 'ROLE_USER');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->email = $input->getArgument('email');
        $user->password = $this->hasher->hashPassword($user, $input->getArgument('password'));

        $role = $input->getOption('role');
        if ($role !== 'ROLE_USER') {
            $user->roles = [$role];
        }

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln("Utilisateur {$user->email} créé avec le rôle {$role} (uuid: {$user->uuid})");

        return Command::SUCCESS;
    }
}
