<?php
/**
 * 2019-06-13.
 */

declare(strict_types=1);

namespace App\Provider;

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\MovieController;
use App\Manager\MovieManager;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Support\Config;
use App\Support\ServiceProviderInterface;
use Aura\Auth\Auth;
use Aura\Auth\Service\LoginService;
use Aura\Auth\Service\LogoutService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use UltraLite\Container\Container;

/**
 * Class WebProvider.
 */
class WebProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     *
     * @return mixed|void
     */
    public function register(Container $container)
    {
        $this->defineControllerDi($container);
        $this->defineRoutes($container);
    }

    /**
     * @param Container $container
     */
    protected function defineControllerDi(Container $container): void
    {
        $container->set(HomeController::class, static function (ContainerInterface $container) {
            return new HomeController(
                $container->get(RouteCollectorInterface::class),
                $container->get(Environment::class),
                $container->get(EntityManagerInterface::class),
                $container->get(MovieManager::class),
                $container->get(AuthService::class),
                $container->get(UserRepository::class),
            );
        });

        $container->set(MovieController::class, static function (ContainerInterface $container) {
            return new MovieController(
                $container->get(RouteCollectorInterface::class),
                $container->get(Environment::class),
                $container->get(EntityManagerInterface::class),
                $container->get(MovieManager::class)
            );
        });

        $container->set(AuthController::class, static function (ContainerInterface $container) {
            return new AuthController(
                $container->get(AuthService::class),
                $container->get(Environment::class),
                $container->get(UserRepository::class),
                $container->get(EntityManagerInterface::class)
            );
        });
    }

    /**
     * @param Container $container
     */
    protected function defineRoutes(Container $container): void
    {
        $router = $container->get(RouteCollectorInterface::class);

        $router->group('/', function (RouteCollectorProxyInterface $router) use ($container) {
            $routes = self::getRoutes($container);
            foreach ($routes as $routeName => $routeConfig) {
                $router->{$routeConfig['method']}($routeConfig['path'] ?? '', $routeConfig['controller'] . ':' . $routeConfig['action'])
                    ->setName($routeName);
            }
        });
    }

    /**
     * @param Container $container
     *
     * @return array
     */
    protected static function getRoutes(Container $container): array
    {
        return Yaml::parseFile($container->get(Config::class)->get('base_dir') . '/config/routes.yaml');
    }
}
