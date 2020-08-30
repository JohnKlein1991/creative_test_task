<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\MovieManager;
use Aura\Auth\Auth;
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
     * @var Auth
     */
    private Auth $authService;

    /**
     * HomeController constructor.
     *
     * @param RouteCollectorInterface $routeCollector
     * @param Environment $twig
     * @param EntityManagerInterface $em
     * @param MovieManager $movieManager
     * @param Auth $authService
     */
    public function __construct(
        RouteCollectorInterface $routeCollector,
        Environment $twig,
        EntityManagerInterface $em,
        MovieManager $movieManager,
        Auth $authService
    ) {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->em = $em;
        $this->movieManager = $movieManager;
        $this->authService = $authService;
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

        $data = $this->twig->render('home/index.html.twig', [
            'trailers' => $movies,
        ]);

        $response->getBody()->write($data);

        return $response;
    }
}
