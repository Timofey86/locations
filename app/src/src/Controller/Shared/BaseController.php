<?php

namespace App\Controller\Shared;

use App\Domain\Shared\Dto\DtoInterface;
use Doctrine\ORM\EntityManagerInterface;
use Money\MoneyFormatter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseController extends AbstractController
{
    public function __construct(
        protected ContainerInterface $container,
        protected MessageBusInterface $commandBus,
        protected MessageBusInterface $queryBus,
        protected MessageBusInterface $mediatorCommandBus,
        protected TranslatorInterface $translator,
        protected MoneyFormatter $moneyFormatter,
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator,
        protected LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();
        $subscribedServices['doctrine.orm.entity_manager'] = EntityManagerInterface::class;
        $subscribedServices['translator'] = TranslatorInterface::class;
        $subscribedServices['validator'] = ValidatorInterface::class;

        return $subscribedServices;
    }

    public function validate(DtoInterface $DTO)
    {
        $violations = $this->validator->validate($DTO);

        if (\count($violations)) {
            throw new ValidationFailedException($DTO, $violations);
        }
    }
}
