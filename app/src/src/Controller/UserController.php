<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Shared\BaseController;
use App\Domain\User\Command\Register\RegisterUserCommand;
use App\Domain\User\Command\Register\RegisterUserDto;
use App\Infrastructure\Queue\BusTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
final class UserController extends BaseController
{
    use BusTrait;

    #[Route('', methods: ['POST'])]
    public function post(Request $request): JsonResponse
    {
        $dto = RegisterUserDto::fromPostRequest($request, $this->serializer);
        $this->validate($dto);
        $command = RegisterUserCommand::create($dto);
        $this->handle($command);

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
