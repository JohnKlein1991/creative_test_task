<?php
/**
 * 2019-06-17.
 */

declare(strict_types=1);

namespace App\Provider;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Support\Config;
use App\Support\ServiceProviderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Psr\Container\ContainerInterface;
use UltraLite\Container\Container;

/**
 * Class DoctrineOrmProvider.
 */
class DoctrineOrmProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $container->set(EntityManager::class, function (ContainerInterface $container): EntityManager {
            $config = $container->get(Config::class);

            $doctrineConfig = Setup::createAnnotationMetadataConfiguration($config->get('doctrine')['mapping'], getenv('APP_ENV') === 'dev');
            $doctrineConfig->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader(), $config->get('doctrine')['mapping']));
            $doctrineConfig->setMetadataCacheImpl(new FilesystemCache($config->get('base_dir') . '/var/cache/doctrine'));

            $connectionConfig = array_merge($config->get('doctrine')['connection'], [
                'url' => sprintf(
                    '%s://%s:%s@%s:%s/%s',
                    getenv('DB_TYPE'),
                    getenv('DB_USER'),
                    getenv('DB_PASSWORD'),
                    getenv('DB_HOST'),
                    getenv('DB_PORT'),
                    getenv('DB_NAME'),
                )
            ]);

            return EntityManager::create($connectionConfig, $doctrineConfig);
        });

        $container->set(EntityManagerInterface::class, static function (ContainerInterface $container): EntityManagerInterface {
            return $container->get(EntityManager::class);
        });

        $container->set(MovieRepository::class, static function (ContainerInterface $container) {
            return new MovieRepository(
                $container->get(EntityManagerInterface::class),
                $container->get(EntityManagerInterface::class)->getClassMetadata(Movie::class)
            );
        });
    }
}
