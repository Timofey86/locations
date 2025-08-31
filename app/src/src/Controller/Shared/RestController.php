<?php

namespace App\Controller\Shared;

use App\Infrastructure\Pagination\Pagination;
use App\Infrastructure\Queue\BusTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Money\MoneyFormatter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class RestController extends BaseController
{
    use BusTrait;

    protected string $entityClass;

    protected string $filterClass;

    protected ?string $listFilterClass = null;

    protected string $normalizerClass;

    protected ?string $listNormalizerClass = null;

    protected bool $usePagination = false;

    protected ?string $paginationNormalizerClass = null;

    /** @var class-string */
    protected string $upsertCommandClass;

    /** @var class-string */
    protected string $upsertDtoClass;

    /** @var class-string */
    protected string $deleteDtoClass;

    /** @var class-string */
    protected string $deleteCommandClass;

    protected array $allowedRoutes = [
        'post' => true,
        'list' => true,
        'index' => true,
        'get' => true,
        'delete' => true,
    ];

    public function __construct(
        protected EntityManagerInterface $entityManager,
        ContainerInterface $container,
        MessageBusInterface $commandBus,
        MessageBusInterface $queryBus,
        MessageBusInterface $mediatorCommandBus,
        TranslatorInterface $translator,
        MoneyFormatter $moneyFormatter,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {

        parent::__construct(
            $container,
            $commandBus,
            $queryBus,
            $mediatorCommandBus,
            $translator,
            $moneyFormatter,
            $serializer,
            $validator,
            $logger
        );
    }

    #[Route('', methods: ['POST'])]
    public function post(Request $request): JsonResponse
    {
        if (false === $this->isRouteAllowed('post')) {
            return new JsonResponse(['message' => 'Route not allowed'], Response::HTTP_BAD_REQUEST);
        }

        $dto = $this->upsertDtoClass::fromPostRequest($request, $this->serializer);
        $dto->setOperationDetails($request);
        $this->validate($dto);
        $command = ($this->upsertCommandClass)::create($dto);
        $this->handle($command);

        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[Route('/list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        if (false === $this->isRouteAllowed('list')) {
            return new JsonResponse(['message' => 'Route not allowed'], Response::HTTP_BAD_REQUEST);
        }

        $params = $request->query->all();
        $repository = $this->getRepository();
        $filter = new $this->listFilterClass($params);
        $data = $repository->findByFilter($filter);

        $serializer = new Serializer([new $this->listNormalizerClass()]);
        $result = $serializer->normalize($data);

        return new Response(
            $this->serializer->serialize(['items' => $result], 'json'),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if (false === $this->isRouteAllowed('index')) {
            return new JsonResponse(['message' => 'Route not allowed'], Response::HTTP_BAD_REQUEST);
        }
        $params = $request->query->all();
        $filter = new $this->filterClass($params);
        $repository = $this->getRepository();

        if ($this->usePagination) {
            $pagination = new Pagination($params);
            $itemsPagination = $repository->findByFilterPaginated($filter, $pagination);

            $serializer = new Serializer([new $this->normalizerClass(), new $this->paginationNormalizerClass()]);
            $result = $serializer->normalize($itemsPagination->getResults());

            return new Response(
                $this->serializer->serialize(
                    [
                        'items' => $result,
                        'pagination' => $serializer->normalize($pagination),
                    ],
                    'json'
                ),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }

        $data = $repository->findByFilter($filter);
        $serializer = new Serializer([new $this->normalizerClass()]);
        $result = $serializer->normalize($data);

        return new Response(
            $this->serializer->serialize(['items' => $result], 'json'),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id, Request $request): JsonResponse
    {
        if (false === $this->isRouteAllowed('delete')) {
            return new JsonResponse(['message' => 'Route not allowed'], Response::HTTP_BAD_REQUEST);
        }

        $repository = $this->getRepository();
        $entity = $repository->find($id);
        if (null === $entity) {
            return new JsonResponse(['message' => 'Resource not found'], Response::HTTP_NOT_FOUND);
        }

        $dto = ($this->deleteDtoClass)::fromArray(['id' => $id], $this->serializer);
        $dto->setOperationDetails($request);
        $this->validate($dto);
        $command = ($this->deleteCommandClass)::create($dto);
        $this->handle($command);

        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(int|string $id): Response
    {
        if (false === $this->isRouteAllowed('get')) {
            return new JsonResponse(['message' => 'Route not allowed'], Response::HTTP_BAD_REQUEST);
        }

        $repository = $this->getRepository();
        $data = $repository->find($id);

        if (null === $data) {
            return new Response(null, Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }

        $serializer = new Serializer([new $this->normalizerClass()]);
        $result = $serializer->normalize($data);

        return new Response(
            $this->serializer->serialize(['item' => $result], 'json'),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository($this->entityClass);
    }

    private function isRouteAllowed(string $route): bool
    {
        return isset($this->allowedRoutes[$route]) && $this->allowedRoutes[$route] === true;
    }
}
