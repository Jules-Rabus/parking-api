<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates or updates a user with hashed password'
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password')
            ->addArgument('last_name', InputArgument::REQUIRED, 'Last name of the user')
            ->addOption('admin', null, InputArgument::OPTIONAL, 'Grant ROLE_ADMIN', false)
            ->addOption('update', null, InputArgument::OPTIONAL, 'Update existing user', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');
        $lastName = $input->getArgument('last_name');
        $isAdmin = (bool)$input->getOption('admin');
        $update = (bool)$input->getOption('update');

        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['email' => $email]);

        if ($user) {
            if (!$update) {
                $io->error(sprintf('User "%s" already exists. Use --update to modify.', $email));
                return Command::FAILURE;
            }
        } else {
            $user = new User();
            $user->setEmail($email);
        }

        if ($isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $user->setLastName($lastName);
        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($update && $user->getId()) {
            $io->success(sprintf('User "%s" successfully updated.', $email));
        } else {
            $io->success(sprintf('User "%s" successfully created.', $email));
        }

        return Command::SUCCESS;
    }
}
