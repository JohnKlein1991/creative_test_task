<?php

namespace App\Manager;

use App\Repository\MovieRepository;

class MovieManager
{
    /**
     * @var MovieRepository
     */
    private MovieRepository $movieRepository;

    /**
     * MovieManager constructor.
     * @param MovieRepository $movieRepository
     */
    public function __construct(MovieRepository $movieRepository)
    {
        $this->movieRepository = $movieRepository;
    }

    public function getAll()
    {
        return $this->movieRepository->findAll();
    }
}