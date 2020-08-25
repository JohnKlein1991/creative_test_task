<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\MovieManager;
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
     * HomeController constructor.
     *
     * @param RouteCollectorInterface $routeCollector
     * @param Environment $twig
     * @param EntityManagerInterface $em
     * @param MovieManager $movieManager
     */
    public function __construct(
        RouteCollectorInterface $routeCollector,
        Environment $twig,
        EntityManagerInterface $em,
        MovieManager $movieManager
    ) {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->em = $em;
        $this->movieManager = $movieManager;
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
