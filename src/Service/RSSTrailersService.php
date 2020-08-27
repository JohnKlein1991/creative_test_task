<?php

namespace App\Service;

/**
 * Интерерфейся для RSS сервисов трейлеров
 *
 * Interface RSSTrailerService
 * @package App\Service
 */
interface RSSTrailersService
{
    /**
     * Возвращает RSS в виде строки
     *
     * @param string|null $url
     * @return string
     */
    public function getData(string $url = null): string;
}
