<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\MovieManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;

/**
 * Class MovieController.
 */
class MovieController
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
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws HttpBadRequestException
     * @throws HttpNotFoundException
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $movieId = isset($args['id']) ? (int) $args['id'] : null;
        if (is_null($movieId)) {
            throw new HttpBadRequestException($request);
        }
        $movie = $this->movieManager->getById($movieId);

        if (is_null($movie)) {
            throw new HttpNotFoundException($request);
        }

        $data = $this->twig->render('movie/show.html.twig', [
            'movie' => $movie,
        ]);

        $response->getBody()->write($data);

        return $response;
    }
}
