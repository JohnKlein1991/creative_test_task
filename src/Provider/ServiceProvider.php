<?php

declare(strict_types=1);

namespace App\Provider;

use App\Manager\MovieManager;
use App\Repository\MovieRepository;
use App\Service\RSSItunesTrailersService;
use App\Service\RSSTrailersService;
use App\Support\Config;
use App\Support\ServiceProviderInterface;
use Aura\Auth\Auth;
use Aura\Auth\AuthFactory;
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

        $container->set(Auth::class, static function (ContainerInterface $container) {
            $authFactory = new AuthFactory($_COOKIE);
            return $authFactory->newInstance();
        });
    }
}
