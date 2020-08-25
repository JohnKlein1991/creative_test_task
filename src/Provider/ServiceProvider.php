<?php
declare(strict_types=1);

namespace App\Provider;

use App\Manager\MovieManager;
use App\Repository\MovieRepository;
use App\Support\ServiceProviderInterface;
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
    }
}
