<?php

namespace App\Manager;

use App\Repository\MovieRepository;

/**
 * Class MovieManager
 * @package App\Manager
 */
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

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->movieRepository->findAll();
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function getById(int $id)
    {
        return $this->movieRepository->find($id);
    }
}
