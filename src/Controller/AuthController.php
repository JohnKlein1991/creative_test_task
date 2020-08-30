<?php

namespace App\Controller;

use Aura\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;

/**
 * Class AuthController
 * @package App\Controller
 */
class AuthController
{
    /**
     * @var Auth
     */
    private Auth $authService;
    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * AuthController constructor.
     * @param Auth $authService
     * @param Environment $twig
     */
    public function __construct(
        Auth $authService,
        Environment $twig
    ) {
        $this->authService = $authService;
        $this->twig = $twig;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->twig->render('auth/login.html.twig');

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->twig->render('auth/register.html.twig');

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->authService->setStatus(false);

        $response->withStatus(302);

        return $response->withHeader('Location', '/');
    }
}
