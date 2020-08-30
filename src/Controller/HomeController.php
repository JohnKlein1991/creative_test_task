<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\MovieManager;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;

/**
 * Class HomeController.
 */
class HomeController
{
    /**
     * @var RouteCollectorInterface
     */
    private RouteCollectorInterface $routeCollector;

    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
    /**
     * @var MovieManager
     */
    private MovieManager $movieManager;
    /**
     * @var AuthService
     */
    private AuthService $authService;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * HomeController constructor.
     *
     * @param RouteCollectorInterface $routeCollector
     * @param Environment $twig
     * @param EntityManagerInterface $em
     * @param MovieManager $movieManager
     * @param AuthService $authService
     * @param UserRepository $userRepository
     */
    public function __construct(
        RouteCollectorInterface $routeCollector,
        Environment $twig,
        EntityManagerInterface $em,
        MovieManager $movieManager,
        AuthService $authService,
        UserRepository $userRepository
    ) {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->em = $em;
        $this->movieManager = $movieManager;
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return ResponseInterface
     *
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $movies = $this->movieManager->getAll();

        $user = null;
        if (!is_null($this->authService->getUsername())) {
            $user = $this->userRepository->findOneBy([
                'username' => $this->authService->getUsername()
            ]);
        }

        $data = $this->twig->render('home/index.html.twig', [
            'trailers' => $movies,
            'user' => $user
        ]);

        $response->getBody()->write($data);

        return $response;
    }
}
