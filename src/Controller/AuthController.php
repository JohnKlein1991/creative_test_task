<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

/**
 * Class AuthController
 * @package App\Controller
 */
class AuthController
{
    /**
     * @var AuthService
     */
    private AuthService $authService;
    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var string[]
     */
    private $requiredRegisterFields = [
        'username',
        'password',
        'password_confirm'
    ];

    /**
     * @var string[]
     */
    private $requiredLoginFields = [
        'username',
        'password',
    ];
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * AuthController constructor.
     * @param AuthService $authService
     * @param Environment $twig
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        AuthService $authService,
        Environment $twig,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        $this->authService = $authService;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $errors = [];

        if ($request->getMethod() === 'POST') {
            $requestBody = $request->getParsedBody();
            $username = $requestBody['username'] ?? null;
            $password = $requestBody['password'] ?? null;

            foreach ($this->requiredLoginFields as $field) {
                if (is_null($requestBody[$field]) || empty($requestBody[$field])) {
                    $errors[] = sprintf('Поле "%s" обязательно к заполнению', $field);
                }
            }

            /** @var User|null $user */
            $user = $this->userRepository->findOneBy([
                'username' => $username
            ]);
            if (is_null($user)) {
                $errors[] = 'Неправильный логин и/или пароль';
            } elseif (!password_verify($password, $user->getPasswordHash())) {
                $errors[] = 'Неправильный логин и/или пароль';
            }

            if (empty($errors)) {
                $this->authService->login($username);

                $response->withStatus(302);
                return $response->withHeader('Location', '/');
            }
        }

        $data = $this->twig->render('auth/login.html.twig', [
            'errors' => $errors
        ]);

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function register(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $errors = [];

        if ($request->getMethod() === 'POST') {
            $requestBody = $request->getParsedBody();
            $username = $requestBody['username'] ?? null;
            $password = $requestBody['password'] ?? null;
            $passwordConfirm = $requestBody['password_confirm'] ?? null;

            foreach ($this->requiredRegisterFields as $field) {
                if (is_null($requestBody[$field]) || empty($requestBody[$field])) {
                    $errors[] = sprintf('Поле "%s" обязательно к заполнению', $field);
                }
            }

            if ($password !== $passwordConfirm) {
                $errors[] = 'Пароли должны совпадать';
            }

            $userWithTheSameName = $this->userRepository->findOneBy([
                'username' => $username
            ]);
            if (!is_null($userWithTheSameName)) {
                $errors[] = 'Пользователь с таким именем уже существует';
            }

            if (empty($errors)) {
                $user = new User();
                $user
                    ->setUsername($username)
                    ->setPasswordHash(password_hash($password, PASSWORD_DEFAULT))
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                ;

                $this->em->persist($user);
                $this->em->flush();

                $this->authService->login($username);

                $response->withStatus(302);
                return $response->withHeader('Location', '/');
            }
        }

        $data = $this->twig->render('auth/register.html.twig', [
            'errors' => $errors
        ]);

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
        $this->authService->logout();

        $response->withStatus(302);

        return $response->withHeader('Location', '/');
    }
}
