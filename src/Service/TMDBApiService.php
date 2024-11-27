<?php

namespace App\Service;

class TMDBApiService
{
    public function __construct(private readonly ApiDataService $apiDataService) {}

    public function getPopularMovies(int $page = 1): array
    {
        return $this->apiDataService->getPopularMovies($page);
    }

    public function searchMovies(string $query, int $page = 1): array
    {
        return $this->apiDataService->searchMovies($query, $page);
    }

    public function getMoviesByGenre(string $genreId, int $page = 1): array
    {
        return $this->apiDataService->getMoviesByGenre($genreId, $page);
    }

    public function getGenres(): array
    {
        return $this->apiDataService->getGenres();
    }

    public function getMovieDetails(int $movieId): array
    {
        return $this->apiDataService->getMovieDetails($movieId);
    }

    public function testConnection(): array
    {
        return $this->apiDataService->testConnection();
    }
}