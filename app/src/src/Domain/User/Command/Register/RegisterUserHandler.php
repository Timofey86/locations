<?php

declare(strict_types=1);

namespace App\Domain\User\Command\Register;

use App\Domain\Shared\Command\CommandHandlerInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Infrastructure\Doctrine\Repository\UserRepository;
use App\Infrastructure\Queue\EventTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterUserHandler implements CommandHandlerInterface
{
    use EventTrait;

    public function __construct(
        protected UserRepository $userRepository,
        protected MessageBusInterface   $eventBus,
        protected UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            new User(),
            $command->password
        );

        $user = User::create(
            id: $command->id,
            email: $command->email,
            hashedPassword:  $hashedPassword,
            roles: $command->roles
        );

        $this->userRepository->save($user);
        $this->userRepository->flush();
    }
}
