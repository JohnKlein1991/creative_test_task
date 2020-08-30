<?php

declare(strict_types=1);

namespace App\Provider;

use App\Manager\MovieManager;
use App\Repository\MovieRepository;
use App\Service\AuthService;
use App\Service\RSSItunesTrailersService;
use App\Service\RSSTrailersService;
use App\Support\Config;
use App\Support\ServiceProviderInterface;
use Aura\Auth\Auth;
use Aura\Auth\AuthFactory;
use Aura\Auth\Service\LoginService;
use Aura\Auth\Service\LogoutService;
use Psr\Container\ContainerInterface;
use UltraLite\Container\Container;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->set(MovieManager::class, static function (ContainerInterface $container) {
            return new MovieManager(
                $container->get(MovieRepository::class),
            );
        });

        $container->set(RSSTrailersService::class, static function (ContainerInterface $container) {
            return new RSSItunesTrailersService(
                $container->get(Config::class)
            );
        });

        $container->set(AuthService::class, static function (ContainerInterface $container) {
            return new AuthService();
        });

        $container->set(Auth::class, static function (ContainerInterface $container) {
            $authFactory = new AuthFactory($_COOKIE);
            return $authFactory->newInstance();
        });

        $container->set(LoginService::class, static function (ContainerInterface $container) {
            $authFactory = new AuthFactory($_COOKIE);
            return $authFactory->newLoginService();
        });

        $container->set(LogoutService::class, static function (ContainerInterface $container) {
            $authFactory = new AuthFactory($_COOKIE);
            return $authFactory->newLogoutService();
        });
    }
}
