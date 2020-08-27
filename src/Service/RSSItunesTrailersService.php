<?php


namespace App\Service;


use App\Support\Config;

/**
 * Class RSSItunesTrailersService
 * @package App\Service
 */
class RSSItunesTrailersService implements RSSTrailersService
{
    /**
     * @var mixed|string|null
     */
    private ?string $defaultUrl;

    /**
     * RSSItunesTrailersService constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->defaultUrl = $config->get('itunes_trailers')['rss_url'] ?? null;
    }

    /**
     * @param string|null $url
     * @return string
     */
    public function getData(string $url = null): string
    {
        if (is_null($url) && is_null($this->defaultUrl)) {
            throw new \LogicException('There is no URL for RSS');
        }

        $targetUrl = $url ?? $this->defaultUrl;

        return file_get_contents($targetUrl);
    }
}